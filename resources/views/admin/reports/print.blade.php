@extends('layouts.print')

@section('title', 'Raport - ' . $student->full_name)

@section('content')
{{-- Bug 4 Fix: gunakan @include partial murni, bukan @extends di sini.
     Blade tidak mendukung @extends di dalam @include yang di-loop. --}}
@include('admin.reports._report_page', [
    'student'      => $student,
    'data'         => $data,
    'note'         => $note,
    'ranking'      => $ranking,
    'semester'     => $semester,
    'academicYear' => $academicYear,
    'class'        => $class,
    'reportType'   => $reportType,
])
@endsection

@section('scripts')
<style>
    #print-bar {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: #1e293b;
        color: white;
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: sans-serif;
        font-size: 10pt;
        z-index: 9998;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    }
    #print-bar button {
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.5rem 1.25rem;
        font-size: 10pt;
        font-weight: bold;
        cursor: pointer;
    }
    #print-bar button:hover {
        background: #4f46e5;
    }
    @media print {
        @page {
        size: A4 portrait;
        margin: 1cm;
    }
        #print-bar { display: none !important; }

        .report-page:last-child {
            page-break-after: avoid !important;
        }
    }
</style>

<div id="print-bar">
    <span>
        📄 <strong>{{ $student->name }}</strong>
        — Semester {{ $semester }} — {{ $academicYear }}
    </span>
    <button onclick="window.print()">🖨️ Cetak Raport</button>
</div>

<script>
    // Tidak auto-print — user klik tombol sendiri
    // Jika dipanggil via AJAX fetch (bulk print), bar ini tidak muncul
    // karena hanya HTML raport yang di-inject, bukan full page
</script>
@endsection
