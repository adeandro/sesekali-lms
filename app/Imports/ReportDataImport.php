<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;

class ReportDataImport implements WithMultipleSheets
{
    use WithConditionalSheets;

    public $academicYear;
    public $semester;

    public function __construct($academicYear, $semester)
    {
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function conditionalSheets(): array
    {
        return [
            0 => new ReportData\AttendanceSheetImport($this->academicYear, $this->semester),
            1 => new ReportData\PersonalitySheetImport($this->academicYear, $this->semester),
            2 => new ReportData\ExtracurricularSheetImport($this->academicYear, $this->semester),
        ];
    }
}
