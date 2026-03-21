<?php

namespace App\Exports;

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularSession;
use App\Models\ExtracurricularSessionAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ExtracurricularRecapExport implements WithMultipleSheets
{
    protected $extracurricular;
    protected $academicYear;
    protected $semester;

    public function __construct(Extracurricular $extracurricular, string $academicYear, int $semester)
    {
        $this->extracurricular = $extracurricular;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function sheets(): array
    {
        return [
            new ExtracurricularAttendanceSheet($this->extracurricular, $this->academicYear, $this->semester),
            new ExtracurricularJournalSheet($this->extracurricular, $this->academicYear, $this->semester),
            new ExtracurricularCoachAttendanceSheet($this->extracurricular, $this->academicYear, $this->semester),
        ];
    }
}

class ExtracurricularAttendanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $extracurricular;
    protected $academicYear;
    protected $semester;

    public function __construct($extracurricular, $academicYear, $semester)
    {
        $this->extracurricular = $extracurricular;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function title(): string
    {
        return 'Rekap Kehadiran Siswa';
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Siswa', 'NIS', 'Kelas', 'Total Pertemuan', 
            'Hadir', 'Izin', 'Sakit', 'Alfa', '% Kehadiran'
        ];
    }

    public function collection()
    {
        $sessions = ExtracurricularSession::where('extracurricular_id', $this->extracurricular->id)
            ->where('academic_year', $this->academicYear)
            ->where('semester', $this->semester)
            ->get();

        $members = ExtracurricularMember::where('extracurricular_id', $this->extracurricular->id)
            ->where('academic_year', $this->academicYear)
            ->with(['student.classroom'])
            ->get();

        $sessionIds = $sessions->pluck('id');
        $rows = [];
        foreach ($members as $index => $member) {
            $attendances = ExtracurricularSessionAttendance::whereIn('session_id', $sessionIds)
                ->where('student_id', $member->student_id)
                ->get();

            $total = $sessions->count();
            $hadir = $attendances->where('status', 'hadir')->count();
            $pct = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $rows[] = [
                'no' => $index + 1,
                'name' => $member->student->name,
                'nis' => $member->student->nis ?? '-',
                'class' => $member->student->classroom->name ?? '-',
                'total' => $total,
                'hadir' => $hadir,
                'izin' => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alfa' => $attendances->where('status', 'alfa')->count(),
                'percentage' => $pct . '%'
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class ExtracurricularJournalSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $extracurricular;
    protected $academicYear;
    protected $semester;

    public function __construct($extracurricular, $academicYear, $semester)
    {
        $this->extracurricular = $extracurricular;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function title(): string
    {
        return 'Jurnal Pertemuan';
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Materi/Topik', 'Catatan', 'Jumlah Hadir'];
    }

    public function collection()
    {
        $sessions = ExtracurricularSession::where('extracurricular_id', $this->extracurricular->id)
            ->where('academic_year', $this->academicYear)
            ->where('semester', $this->semester)
            ->withCount(['studentAttendances as hadir_count' => function($query) {
                $query->where('status', 'hadir');
            }])
            ->orderBy('date')
            ->get();

        $rows = [];
        foreach ($sessions as $index => $session) {
            $rows[] = [
                'no' => $index + 1,
                'date' => $session->date->format('d/m/Y'),
                'topic' => $session->topic,
                'notes' => $session->notes ?? '-',
                'hadir_count' => $session->hadir_count
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class ExtracurricularCoachAttendanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $extracurricular;
    protected $academicYear;
    protected $semester;

    public function __construct($extracurricular, $academicYear, $semester)
    {
        $this->extracurricular = $extracurricular;
        $this->academicYear    = $academicYear;
        $this->semester        = $semester;
    }

    public function title(): string
    {
        return 'Kehadiran Pembina';
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Pembina', 'Total Pertemuan',
            'Hadir', 'Tidak Hadir', '% Kehadiran'
        ];
    }

    public function collection()
    {
        $sessions = \App\Models\ExtracurricularSession
            ::where('extracurricular_id', $this->extracurricular->id)
            ->where('academic_year', $this->academicYear)
            ->where('semester', $this->semester)
            ->get();

        $coaches = \App\Models\ExtracurricularCoach
            ::where('extracurricular_id', $this->extracurricular->id)
            ->where('academic_year', $this->academicYear)
            ->with('teacher')
            ->get();

        $sessionIds = $sessions->pluck('id');
        $total      = $sessions->count();
        $rows       = [];

        foreach ($coaches as $index => $coach) {
            $attendances = \App\Models\ExtracurricularCoachAttendance
                ::whereIn('session_id', $sessionIds)
                ->where('coach_id', $coach->teacher_id)
                ->get();

            $hadir      = $attendances->where('status', 'hadir')->count();
            $tidakHadir = $attendances->where('status', 'tidak_hadir')->count();
            $pct        = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $rows[] = [
                'no'          => $index + 1,
                'name'        => $coach->teacher->name ?? '-',
                'total'       => $total,
                'hadir'       => $hadir,
                'tidak_hadir' => $tidakHadir,
                'percentage'  => $pct . '%',
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
