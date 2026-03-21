<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\ExtracurricularCoach;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use App\Models\ExtracurricularSessionAttendance;
use App\Models\ExtracurricularCoachAttendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExtracurricularSessionController extends Controller
{
    // Helper: cek apakah user adalah coach ekskul ini
    private function isCoach(Extracurricular $extracurricular, string $academicYear): bool
    {
        if (auth()->user()->role === 'superadmin') return true;
        return ExtracurricularCoach::where('extracurricular_id', $extracurricular->id)
            ->where('teacher_id', auth()->id())
            ->where('academic_year', $academicYear)
            ->exists();
    }

    // List semua session per ekskul
    public function index(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester = (int) ($request->semester ?? 1);

        if (!$this->isCoach($extracurricular, $academicYear)) abort(403);

        $sessions = ExtracurricularSession
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->withCount('studentAttendances')
            ->orderByDesc('date')
            ->get();

        // Ambil semua coach untuk ekskul ini
        $coaches = ExtracurricularCoach
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with('teacher')
            ->get();

        return view('admin.extracurriculars.sessions.index', compact(
            'extracurricular', 'sessions', 'academicYear', 'semester', 'coaches'
        ));
    }

    // Form buat session baru
    public function create(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester = (int) ($request->semester ?? 1);

        if (!$this->isCoach($extracurricular, $academicYear)) abort(403);

        // Ambil anggota siswa untuk pre-fill presensi
        $members = ExtracurricularMember
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with(['student.classroom'])
            ->get();

        // Ambil semua coach untuk presensi pembina
        $coaches = ExtracurricularCoach
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with('teacher')
            ->get();

        return view('admin.extracurriculars.sessions.create', compact(
            'extracurricular', 'members', 'coaches', 'academicYear', 'semester'
        ));
    }

    // Simpan session baru + presensi
    public function store(Request $request, Extracurricular $extracurricular)
    {
        $request->validate([
            'academic_year'  => 'required|string',
            'semester'       => 'required|in:1,2',
            'date'           => 'required|date',
            'topic'          => 'required|string|max:255',
            'notes'          => 'nullable|string',
            'attendances'    => 'nullable|array',
            'attendances.*'  => 'in:hadir,izin,sakit,alfa',
            'coach_attendances'    => 'nullable|array',
            'coach_attendances.*'  => 'in:hadir,tidak_hadir',
            'attendance_notes'     => 'nullable|array',
        ]);

        if (!$this->isCoach($extracurricular, $request->academic_year)) {
            abort(403);
        }

        // Buat session
        $session = ExtracurricularSession::create([
            'extracurricular_id' => $extracurricular->id,
            'academic_year'      => $request->academic_year,
            'semester'           => $request->semester,
            'date'               => $request->date,
            'topic'              => $request->topic,
            'notes'              => $request->notes,
            'created_by'         => auth()->id(),
        ]);

        // Simpan presensi siswa
        foreach ($request->attendances ?? [] as $studentId => $status) {
            ExtracurricularSessionAttendance::create([
                'session_id' => $session->id,
                'student_id' => $studentId,
                'status'     => $status,
                'note'       => $request->attendance_notes[$studentId] ?? null,
            ]);
        }

        // Simpan presensi pembina
        foreach ($request->coach_attendances ?? [] as $coachId => $status) {
            ExtracurricularCoachAttendance::create([
                'session_id'  => $session->id,
                'coach_id'    => $coachId,
                'status'      => $status,
                'recorded_by' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('admin.extracurriculars.sessions.index', [
                'extracurricular' => $extracurricular->id,
                'academic_year'   => $request->academic_year,
                'semester'        => $request->semester,
            ])
            ->with('success', 'Pertemuan berhasil dicatat.');
    }

    // Detail session (lihat presensi)
    public function show(Request $request, Extracurricular $extracurricular, ExtracurricularSession $session)
    {
        $this->authorize_session($extracurricular, $session);

        $studentAttendances = $session->studentAttendances()
            ->with(['student.classroom'])
            ->get();

        $coachAttendances = $session->coachAttendances()
            ->with(['coach', 'recorder'])
            ->get();

        return view('admin.extracurriculars.sessions.show', compact(
            'extracurricular', 'session', 
            'studentAttendances', 'coachAttendances'
        ));
    }

    // Hapus session
    public function destroy(Extracurricular $extracurricular, ExtracurricularSession $session)
    {
        $this->authorize_session($extracurricular, $session);
        $session->delete();
        return back()->with('success', 'Pertemuan berhasil dihapus.');
    }

    // Rekap kehadiran per semester
    public function recap(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester = (int) ($request->semester ?? 1);

        if (!$this->isCoach($extracurricular, $academicYear)) abort(403);

        $sessions = ExtracurricularSession
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get();

        $members = ExtracurricularMember
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with(['student.classroom'])
            ->get();

        $sessionIds = $sessions->pluck('id');

        // Hitung rekap per siswa
        $recap = [];
        foreach ($members as $member) {
            $attendances = ExtracurricularSessionAttendance
                ::whereIn('session_id', $sessionIds)
                ->where('student_id', $member->student_id)
                ->get();

            $recap[] = [
                'student'       => $member->student,
                'total_sessions'=> $sessions->count(),
                'hadir'         => $attendances->where('status', 'hadir')->count(),
                'izin'          => $attendances->where('status', 'izin')->count(),
                'sakit'         => $attendances->where('status', 'sakit')->count(),
                'alfa'          => $attendances->where('status', 'alfa')->count(),
                'pct_hadir'     => $sessions->count() > 0 
                    ? round($attendances->where('status','hadir')->count() 
                        / $sessions->count() * 100) 
                    : 0,
            ];
        }

        // Hitung rekap kehadiran pembina
        $coaches = \App\Models\ExtracurricularCoach
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with('teacher')
            ->get();

        $coachRecap = [];
        foreach ($coaches as $coach) {
            $coachAttendances = \App\Models\ExtracurricularCoachAttendance
                ::whereIn('session_id', $sessionIds)
                ->where('coach_id', $coach->teacher_id)
                ->get();

            $hadir      = $coachAttendances->where('status', 'hadir')->count();
            $tidakHadir = $coachAttendances->where('status', 'tidak_hadir')->count();
            $total      = $sessions->count();

            $coachRecap[] = [
                'name'        => $coach->teacher->name ?? '-',
                'total'       => $total,
                'hadir'       => $hadir,
                'tidak_hadir' => $tidakHadir,
                'pct_hadir'   => $total > 0 ? round(($hadir / $total) * 100) : 0,
            ];
        }

        return view('admin.extracurriculars.sessions.recap', compact(
            'extracurricular', 'sessions', 'recap', 'coachRecap', 'academicYear', 'semester'
        ));
    }

    // Export Excel
    public function exportExcel(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester     = (int) ($request->semester ?? 1);

        if (!$this->isCoach($extracurricular, $academicYear)) abort(403);

        $filename = 'Rekap_' . str_replace(' ', '_', $extracurricular->name) 
            . '_Sem' . $semester . '_' 
            . str_replace('/', '-', $academicYear) . '.xlsx';

        return Excel::download(
            new \App\Exports\ExtracurricularRecapExport(
                $extracurricular, $academicYear, $semester
            ),
            $filename
        );
    }

    // Export PDF
    public function exportPdf(Request $request, Extracurricular $extracurricular)
    {
        $academicYear = $request->academic_year 
            ?? Setting::get('academic_year', '2024/2025');
        $semester     = (int) ($request->semester ?? 1);

        if (!$this->isCoach($extracurricular, $academicYear)) abort(403);

        // Gunakan data yang sama dengan recap
        $sessions = ExtracurricularSession
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get();

        $members = ExtracurricularMember
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with(['student.classroom'])
            ->get();

        $sessionIds = $sessions->pluck('id');
        $recap = [];
        foreach ($members as $member) {
            $attendances = ExtracurricularSessionAttendance
                ::whereIn('session_id', $sessionIds)
                ->where('student_id', $member->student_id)
                ->get();
            $recap[] = [
                'student'        => $member->student,
                'total_sessions' => $sessions->count(),
                'hadir'          => $attendances->where('status', 'hadir')->count(),
                'izin'           => $attendances->where('status', 'izin')->count(),
                'sakit'          => $attendances->where('status', 'sakit')->count(),
                'alfa'           => $attendances->where('status', 'alfa')->count(),
                'pct_hadir'      => $sessions->count() > 0 
                    ? round($attendances->where('status','hadir')->count() 
                        / $sessions->count() * 100) 
                    : 0,
            ];
        }

        // Hitung rekap kehadiran pembina
        $coaches = \App\Models\ExtracurricularCoach
            ::where('extracurricular_id', $extracurricular->id)
            ->where('academic_year', $academicYear)
            ->with('teacher')
            ->get();

        $coachRecap = [];
        foreach ($coaches as $coach) {
            $coachAttendances = \App\Models\ExtracurricularCoachAttendance
                ::whereIn('session_id', $sessionIds)
                ->where('coach_id', $coach->teacher_id)
                ->get();

            $hadir      = $coachAttendances->where('status', 'hadir')->count();
            $tidakHadir = $coachAttendances->where('status', 'tidak_hadir')->count();
            $total      = $sessions->count();

            $coachRecap[] = [
                'name'        => $coach->teacher->name ?? '-',
                'total'       => $total,
                'hadir'       => $hadir,
                'tidak_hadir' => $tidakHadir,
                'pct_hadir'   => $total > 0 ? round(($hadir / $total) * 100) : 0,
            ];
        }

        $configs = Setting::pluck('value', 'key')->toArray();

        $pdf = Pdf::loadView(
            'admin.extracurriculars.sessions.recap-pdf',
            compact('extracurricular', 'sessions', 'recap', 'coachRecap',
                    'academicYear', 'semester', 'configs')
        )->setPaper('a4', 'landscape');

        $filename = 'Rekap_' . str_replace(' ', '_', $extracurricular->name)
            . '_Sem' . $semester . '_'
            . str_replace('/', '-', $academicYear) . '.pdf';

        return $pdf->download($filename);
    }

    // Helper private: validasi session milik ekskul ini
    private function authorize_session(Extracurricular $extracurricular, ExtracurricularSession $session): void
    {
        if ($session->extracurricular_id !== $extracurricular->id) abort(404);
        if (!$this->isCoach($extracurricular, $session->academic_year)) abort(403);
    }
}
