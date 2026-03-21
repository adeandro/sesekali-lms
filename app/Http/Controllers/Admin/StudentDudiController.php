<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\StudentDudi;
use App\Models\User;
use App\Imports\StudentDudiImport;
use App\Exports\StudentDudiTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StudentDudiController extends Controller
{
    // ── RBAC Helpers ───────────────────────────────────────────────────

    /**
     * Ambil kelas yang boleh diakses user (superadmin = semua, teacher = wali kelas).
     */
    private function getAccessibleClasses()
    {
        $user = Auth::user();
        if ($user->role === 'superadmin') {
            return ClassRoom::active()->orderBy('name', 'asc')->get();
        }
        // Teacher: hanya kelas di mana dia wali kelas
        return ClassRoom::where('homeroom_teacher_id', '=', $user->id)
            ->active()
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Cek apakah user boleh akses kelas tertentu.
     */
    private function authorizeClass(int $classId): void
    {
        $user = Auth::user();
        if ($user->role === 'superadmin') return;

        $allowed = ClassRoom::active()
            ->where('id', $classId)
            ->where('homeroom_teacher_id', $user->id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
    }

    /**
     * Tampilkan daftar siswa dan ringkasan DU/DI.
     */
    public function index(Request $request)
    {
        $classes = $this->getAccessibleClasses();
        $classId = $request->query('class_id');
        $semester = $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));

        // Jika teacher punya tepat 1 kelas, auto-select
        $user = Auth::user();
        if ($user->role === 'teacher') {
            if ($classes->count() === 1 && !$classId) {
                $classId = $classes->first()->id;
            }
            if ($classes->isEmpty()) {
                session()->now('info', 'Anda tidak terdaftar sebagai wali kelas manapun.');
            }
        }

        $students = [];
        if ($classId) {
            // Pastikan authorize jika akses class_id tertentu
            $this->authorizeClass((int)$classId);

            $students = User::where('role', '=', 'student')
                ->where('class_id', '=', $classId)
                ->orderBy('name', 'asc')
                ->get();
            
            foreach ($students as $student) {
                $student->dudi_count = StudentDudi::where('student_id', '=', $student->id)
                    ->where('semester', '=', $semester)
                    ->where('academic_year', '=', $academicYear)
                    ->count();
            }
        }

        return view('admin.dudi.index', compact(
            'classes', 'students', 'classId', 'semester', 'academicYear'
        ));
    }

    /**
     * Form input/edit kegiatan DU/DI untuk 1 siswa.
     */
    public function edit(Request $request, User $student)
    {
        $this->authorizeClass($student->class_id);

        $semester = $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));

        $dudis = StudentDudi::where('student_id', '=', $student->id)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.dudi.edit', compact(
            'student', 'dudis', 'semester', 'academicYear'
        ));
    }

    /**
     * Update data DU/DI dengan strategi "Replace".
     */
    public function update(Request $request, User $student)
    {
        $request->validate([
            'semester' => 'required|integer|in:1,2',
            'academic_year' => 'required|string|max:20',
            'dudis' => 'nullable|array',
            'dudis.*.activity_name' => 'required_with:dudis|string|max:191',
            'dudis.*.institution_name' => 'required_with:dudis|string|max:191',
            'dudis.*.institution_address' => 'nullable|string|max:191',
            'dudis.*.period' => 'required_with:dudis|string|max:100',
            'dudis.*.grade' => 'nullable|string|max:20',
        ]);

        $semester = $request->semester;
        $academicYear = $request->academic_year;

        $this->authorizeClass($student->class_id);

        // Start Transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($student, $semester, $academicYear, $request) {
            // Hapus data lama
            StudentDudi::where('student_id', '=', $student->id)
                ->where('semester', '=', $semester)
                ->where('academic_year', '=', $academicYear)
                ->delete();

            // Insert data baru
            if ($request->has('dudis')) {
                foreach ($request->dudis as $index => $data) {
                    StudentDudi::create([
                        'student_id' => $student->id,
                        'class_id' => $student->class_id,
                        'teacher_id' => Auth::id(),
                        'semester' => $semester,
                        'academic_year' => $academicYear,
                        'activity_name' => $data['activity_name'],
                        'institution_name' => $data['institution_name'],
                        'institution_address' => $data['institution_address'] ?? null,
                        'period' => $data['period'],
                        'grade' => $data['grade'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return redirect()->route('admin.dudi.index', [
            'class_id' => $student->class_id,
            'semester' => $semester,
            'academic_year' => $academicYear
        ])->with('success', 'Data DU/DI berhasil diperbarui untuk ' . $student->name);
    }

    /**
     * Import data dari Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'class_id' => 'required|exists:classes,id',
            'semester' => 'required|integer|in:1,2',
            'academic_year' => 'required|string|max:20',
        ]);

        $this->authorizeClass((int)$request->class_id);

        Excel::import(new StudentDudiImport(
            $request->class_id,
            $request->semester,
            $request->academic_year,
            Auth::id()
        ), $request->file('file'));

        return back()->with('success', 'Data DU/DI berhasil diimport.');
    }

    /**
     * Download template Excel.
     */
    public function downloadTemplate(Request $request)
    {
        $classId = $request->query('class_id');
        
        if ($classId) {
            $this->authorizeClass((int)$classId);
        }

        return Excel::download(new StudentDudiTemplateExport($classId), 'template_dudi.xlsx');
    }
}
