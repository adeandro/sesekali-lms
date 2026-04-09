<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularCoach;
use App\Models\StudentExtracurricular;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;

class ExtracurricularController extends Controller
{
    /**
     * Dashboard guru pembina — lihat ekskul yang dibina.
     */
    public function myAssignments(Request $request)
    {
        $user         = auth()->user();
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');

        // Show only extracurriculars assigned to the current user (Personalized view)
        // Even superadmins see only their own assignments here.
        // For 'all view', use the main index page.
        $assignments = ExtracurricularCoach::where('teacher_id', $user->id)
            ->where('academic_year', '=', $academicYear)
            ->with('extracurricular')
            ->get()
            ->unique('extracurricular_id');

        return view('admin.extracurriculars.my-assignments', compact(
            'assignments', 'academicYear'
        ));
    }

    /**
     * Halaman input nilai per ekskul per semester.
     */
    public function gradesForm(Request $request, Extracurricular $extracurricular)
    {
        $user         = auth()->user();
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester     = (int) ($request->semester ?? 1);

        // Validasi akses — hanya coach ekskul ini atau superadmin
        if ($user->role !== 'superadmin') {
            $isCoach = ExtracurricularCoach::where('extracurricular_id', $extracurricular->id)
                ->where('teacher_id', $user->id)
                ->where('academic_year', '=', $academicYear)
                ->exists();

            if (!$isCoach) {
                abort(403, 'Anda bukan pembina ekskul ini.');
            }
        }

        // Ambil anggota siswa
        $members = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', '=', $academicYear)
            ->with('student.classroom')
            ->get();

        // Ambil nilai yang sudah ada
        $existingGrades = StudentExtracurricular::whereIn('student_id', $members->pluck('student_id'))
            ->where('name', $extracurricular->name)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->get()
            ->keyBy('student_id');

        return view('admin.extracurriculars.grades', compact(
            'extracurricular', 'members', 'existingGrades',
            'academicYear', 'semester'
        ));
    }

    /**
     * Simpan nilai ekstrakurikuler.
     */
    public function gradesSave(Request $request, Extracurricular $extracurricular)
    {
        $user = auth()->user();
        $academicYear = $request->academic_year;
        $semester     = (int) $request->semester;

        // Validasi akses
        if ($user->role !== 'superadmin') {
            $isCoach = ExtracurricularCoach::where('extracurricular_id', $extracurricular->id)
                ->where('teacher_id', $user->id)
                ->where('academic_year', '=', $academicYear)
                ->exists();
            if (!$isCoach) abort(403, 'Unauthorized.');
        }

        $request->validate([
            'academic_year' => 'required|string',
            'semester'      => 'required|in:1,2',
            'grades'        => 'nullable|array',
            'grades.*'      => 'nullable|in:Sangat Baik,Baik,Cukup,Kurang',
            'notes'         => 'nullable|array',
            'notes.*'       => 'nullable|string|max:500',
        ]);

        // Ambil anggota untuk validasi
        $memberIds = ExtracurricularMember::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', '=', $academicYear)
            ->pluck('student_id')
            ->toArray();

        foreach ($request->grades ?? [] as $studentId => $grade) {
            // Pastikan student adalah member ekskul ini
            if (!in_array($studentId, $memberIds)) continue;
            if (empty($grade)) continue;

            $student = User::find($studentId);
            if (!$student) continue;

            StudentExtracurricular::updateOrCreate(
                [
                    'student_id'    => $studentId,
                    'name'          => $extracurricular->name,
                    'semester'      => $semester,
                    'academic_year' => $academicYear,
                ],
                [
                    'class_id'   => $student->class_id,
                    'teacher_id' => auth()->id(),
                    'grade'      => $grade,
                    'note'       => $request->notes[$studentId] ?? null,
                ]
            );
        }

        return back()->with('success', 'Nilai ekskul berhasil disimpan.');
    }
    /**
     * Halaman utama modul ekskul.
     */
    public function index(Request $request)
    {
        $academicYear = $request->academic_year 
            ?? \App\Models\Setting::get('academic_year', '2024/2025');
        
        $extracurriculars = Extracurricular::withCount([
            'coaches' => function($q) use ($academicYear) {
                return $q->where('academic_year', '=', $academicYear);
            },
            'members' => function($q) use ($academicYear) {
                return $q->where('academic_year', $academicYear);
            },
        ])->orderBy('sort_order')->get();

        return view('admin.extracurriculars.index', compact(
            'extracurriculars', 'academicYear'
        ));
    }

    /**
     * Detail 1 ekskul — kelola coach & member.
     */
    public function show(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? \App\Models\Setting::get('academic_year', '2024/2025');

        $coaches = $extracurricular->coaches()
            ->where('academic_year', $academicYear)
            ->with('teacher')
            ->get();

        $members = $extracurricular->members()
            ->where('academic_year', $academicYear)
            ->with('student.classRoom')
            ->get();

        // Daftar guru (atau superadmin) yang belum jadi coach di ekskul ini
        $availableTeachers = User::whereIn('role', ['teacher', 'superadmin'])
            ->whereNotIn('id', $coaches->pluck('teacher_id'))
            ->orderBy('role', 'desc')
            ->orderBy('name')
            ->get();

        // Daftar siswa aktif yang belum jadi member di ekskul ini
        $availableStudents = User::where('role', 'student')
            ->whereNotIn('id', $members->pluck('student_id'))
            ->with('classroom')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'nis'   => $s->nis ?? '-',
                'class' => $s->classroom->name ?? 'Tanpa Kelas',
            ]);

        return view('admin.extracurriculars.show', compact(
            'extracurricular', 'coaches', 'members',
            'availableTeachers', 'availableStudents', 'academicYear'
        ));
    }

    /**
     * Store a new extracurricular.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $extracurricular = Extracurricular::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => Extracurricular::max('sort_order') + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $extracurricular
            ]);
        }

        return back()->with('success', 'Ekskul baru berhasil ditambahkan.');
    }

    /**
     * Update active status or other fields.
     */
    public function update(Request $request, Extracurricular $extracurricular)
    {
        $extracurricular->update($request->only('is_active', 'name', 'description'));

        if ($request->wantsJson()) {
             return response()->json(['success' => true]);
        }

        return back()->with('success', 'Status ekskul berhasil diperbarui.');
    }

    /**
     * Reorder extracurriculars.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:extracurriculars,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Extracurricular::where('id', $id)->update([
                'sort_order' => $index + 1
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified extracurricular.
     */
    public function destroy(Extracurricular $extracurricular)
    {
        // Check if there are students associated (Legacy system uses student_extracurriculars)
        // or check members/coaches in the new system
        if ($extracurricular->members()->exists() || $extracurricular->coaches()->exists()) {
            $msg = 'Tidak bisa menghapus ekskul yang sudah memiliki data guru atau siswa.';
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            return back()->with('error', $msg);
        }

        $extracurricular->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.extracurriculars.index')->with('success', 'Ekskul berhasil dihapus.');
    }

    /**
     * Tambah coach.
     */
    public function addCoach(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'teacher_id'    => 'required|exists:users,id',
            'academic_year' => 'required|string',
        ]);

        ExtracurricularCoach::firstOrCreate([
            'extracurricular_id' => $extracurricular->id,
            'teacher_id'         => $request->teacher_id,
            'academic_year'      => $request->academic_year,
        ], ['is_active' => true]);

        return back()->with('success', 'Guru pembina berhasil ditambahkan.');
    }

    /**
     * Hapus coach.
     */
    public function removeCoach(Extracurricular $extracurricular, ExtracurricularCoach $coach)
    {
        $coach->delete();
        return back()->with('success', 'Guru pembina berhasil dihapus.');
    }

    /**
     * Tambah member (bisa bulk).
     */
    public function addMembers(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'academic_year' => 'required|string',
        ]);

        foreach ($request->student_ids as $studentId) {
            ExtracurricularMember::firstOrCreate([
                'extracurricular_id' => $extracurricular->id,
                'student_id'         => $studentId,
                'academic_year'      => $request->academic_year,
            ]);
        }

        return back()->with('success', count($request->student_ids) . ' siswa berhasil ditambahkan.');
    }

    /**
     * Hapus member.
     */
    public function removeMember(Extracurricular $extracurricular, ExtracurricularMember $member)
    {
        $member->delete();
        return back()->with('success', 'Siswa berhasil dihapus dari ekskul.');
    }

    /**
     * Update nama/deskripsi ekskul (form edit).
     */
    public function edit(Extracurricular $extracurricular)
    {
        return view('admin.extracurriculars.edit', compact('extracurricular'));
    }

    /**
     * Update detail ekskul.
     */
    public function updateDetail(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $extracurricular->update($request->only('name', 'description'));
        return redirect()->route('admin.extracurriculars.index')->with('success', 'Ekskul berhasil diperbarui.');
    }
}
