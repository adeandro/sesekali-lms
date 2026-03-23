<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\User; // For students
use App\Models\StudentAttendance;
use App\Models\StudentPersonality;
use App\Imports\ReportDataImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportDataController extends Controller
{
    // ── RBAC Helpers ───────────────────────────────────────────────────

    private function getAccessibleClasses()
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            return ClassRoom::active()->orderBy('grade', 'asc')->orderBy('name', 'asc')->get();
        }
        return ClassRoom::where('homeroom_teacher_id', $user->id)
            ->active()
            ->orderBy('grade', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    private function authorizeClass(int $classId): void
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') return;

        $allowed = ClassRoom::active()
            ->where('id', $classId)
            ->where('homeroom_teacher_id', $user->id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
    }

    public function index()
    {
        return redirect()->route('admin.report-data.student-data');
    }

    public function importForm()
    {
        $academicYear = Setting::get('academic_year');
        // Currently we only need semester & academic year, typically we let teachers choose or pick from settings
        return view('admin.report-data.import', compact('academicYear'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'semester' => 'required|in:ganjil,genap',
            'academic_year' => 'required|string',
        ]);

        try {
            $this->authorizeClass((int)$request->class_id ?? 0); // Need to check if class_id provided in import (usually yes for reports)
            
            Excel::import(new ReportDataImport($request->academic_year, $request->semester), $request->file('file'));
            return redirect()->route('admin.report-data.index')->with('success', 'Data Raport berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\ReportDataTemplateExport, 'Template_Import_Data_Raport.xlsx');
    }

    /**
     * Display student report data form (attendance, personality, extracurriculars)
     */
    public function studentDataForm(Request $request)
    {
        $academicYear = Setting::get('academic_year');
        $classes = $this->getAccessibleClasses();
        $classId = $request->class_id;
        $semester = $request->semester ?? 'ganjil';
        $semesterInt = $semester === 'ganjil' ? 1 : 2;

        // Auto-select for teacher with 1 class
        $user = auth()->user();
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
            $this->authorizeClass((int)$classId);

            $students = User::where('role', '=', 'student')
                ->where('class_id', '=', $classId)
                ->aktif()
                ->orderBy('name', 'asc')
                ->with([
                    'attendance' => function($q) use ($semesterInt, $academicYear) {
                        $q->where('semester', '=', $semesterInt)->where('academic_year', '=', $academicYear);
                    },
                    'personality' => function($q) use ($semesterInt, $academicYear) {
                        $q->where('semester', '=', $semesterInt)->where('academic_year', '=', $academicYear);
                    }
                ])
                ->get();
        }

        return view('admin.report-data.student-data', compact('academicYear', 'classes', 'students', 'classId', 'semester'));
    }

    /**
     * Save student report data
     */
    public function saveStudentData(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
            'class_id' => 'required|exists:classes,id',
            'students' => 'required|array',
        ]);

        $semesterInt = $request->semester === 'ganjil' ? 1 : 2;
        $academicYear = $request->academic_year;

        $classId = $request->class_id;
        $this->authorizeClass((int)$classId);
        
        $teacherId = auth()->id();

        DB::transaction(function() use ($request, $semesterInt, $academicYear, $classId, $teacherId) {
            foreach ($request->students as $studentId => $data) {
                // 1. Attendance
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $studentId, 
                        'semester' => $semesterInt, 
                        'academic_year' => $academicYear
                    ],
                    [
                        'class_id' => $classId,
                        'sick_days' => $data['attendance']['sick_days'] ?? 0,
                        'permit_days' => $data['attendance']['permit_days'] ?? 0,
                        'alpha_days' => $data['attendance']['alpha_days'] ?? 0,
                    ]
                );

                // 2. Personality
                StudentPersonality::updateOrCreate(
                    [
                        'student_id' => $studentId, 
                        'semester' => $semesterInt, 
                        'academic_year' => $academicYear
                    ],
                    [
                        'class_id' => $classId,
                        'teacher_id' => $teacherId,
                        'discipline' => $data['personality']['discipline'] ?? null,
                        'behavior' => $data['personality']['behavior'] ?? null,
                        'neatness' => $data['personality']['neatness'] ?? null,
                    ]
                );

            }
        });

        return redirect()->back()->with('success', 'Data Raport Siswa berhasil disimpan.');
    }

    /**
     * Display class average input form
     */
    public function classAverageForm(Request $request)
    {
        $academicYear = Setting::get('academic_year');
        $classes = $this->getAccessibleClasses();
        $classId = $request->class_id;
        $semester = $request->semester ?? 'ganjil';

        // Filter subjects to show in the table
        $subjects = Subject::orderBy('category', 'asc')->orderBy('name', 'asc')->get();

        return view('admin.report-data.class-average', compact('academicYear', 'classes', 'subjects', 'classId', 'semester'));
    }

    /**
     * Save class average data
     */
    public function saveClassAverage(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester' => 'required|in:ganjil,genap',
            'averages' => 'required|array',
        ]);

        $semesterInt = $request->semester === 'ganjil' ? 1 : 2;
        $academicYear = $request->academic_year;
        $teacherId = auth()->id();

        DB::transaction(function() use ($request, $semesterInt, $academicYear, $teacherId) {
            foreach ($request->averages as $classId => $subjectAverages) {
                $this->authorizeClass((int)$classId);
                $class = ClassRoom::findOrFail($classId);
                $jenjang = $class->grade;

                foreach ($subjectAverages as $subjectId => $averageValue) {
                    if ($averageValue !== null && $averageValue !== '') {
                        \App\Models\ClassGradeAverage::updateOrCreate(
                            [
                                'class_id' => $classId,
                                'subject_id' => $subjectId,
                                'semester' => $semesterInt,
                                'academic_year' => $academicYear,
                            ],
                            [
                                'teacher_id' => $teacherId,
                                'jenjang' => $jenjang,
                                'class_average' => $averageValue,
                            ]
                        );
                    } else {
                        // Optional: delete if empty? Usually just leave it.
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Rata-rata kelas berhasil disimpan.');
    }
}
