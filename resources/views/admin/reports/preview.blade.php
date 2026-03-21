@extends('layouts.app')

@section('title', 'Preview Raport — ' . $student->name)

@section('content')
@push('styles')
<style>
    .report-preview-container {
        background-color: #0f172a; /* Custom Dark Slate */
        background-image: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%);
        padding: 3rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 4rem;
        align-items: center;
        overflow-y: auto;
        max-height: calc(100vh - 180px);
        border-radius: 1.5rem;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
        counter-reset: page-counter;
    }

    .report-page {
        width: 210mm;
        min-height: 297mm;
        background: white;
        padding: 1.5cm 1.5cm 1.5cm 2cm; /* Standard Margins: Top, Right, Bottom, Left (2cm for binding) */
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        position: relative;
        margin: 40px auto; /* space for the absolute label */
        transition: transform 0.3s ease;
    }

    .report-page::before {
        counter-increment: page-counter;
        content: "HALAMAN " counter(page-counter);
        position: absolute;
        top: -3rem;
        left: 50%;
        transform: translateX(-50%);
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.2em;
        padding: 0.5rem 1.5rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 9999px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        white-space: nowrap;
        pointer-events: none;
    }

    .report-page:hover {
        transform: translateY(-5px);
    }

    /* Hide the page-break-after in preview as we use gap */
    .report-page {
        page-break-after: auto !important;
        margin-bottom: 0 !important;
    }

    @media (max-width: 1024px) {
        .report-page {
            width: 100%;
            min-height: auto;
            padding: 1rem;
        }
        .report-preview-container {
            padding: 1.5rem 0.5rem;
            gap: 2rem;
        }
    }
</style>
@endpush

<div class="space-y-4">

    {{-- ── Breadcrumb & Actions ────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="hover:text-[var(--brand-primary)] transition-colors">Raport</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-gray-600 font-bold">Preview</span>
            </div>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">
                {{ $student->name }}
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $class->name ?? '-' }} &bull;
                Semester {{ $semester == 1 ? 'Ganjil' : 'Genap' }} &bull;
                {{ $academicYear }}
            </p>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.reports.index') }}?class_id={{ $student->class_id }}&semester={{ $semester }}&academic_year={{ $academicYear }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left text-xs"></i> Kembali
            </a>
            <a href="{{ route('admin.reports.printSingle', $student->id) . '?' . http_build_query(['semester' => $semester, 'academic_year' => $academicYear, 'report_type' => $reportType]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--brand-primary)] text-white text-sm font-bold rounded-xl hover:opacity-90 transition-all shadow-lg shadow-[var(--brand-glow)]">
                <i class="fas fa-print text-xs"></i> Cetak Raport
            </a>
        </div>
    </div>

    {{-- ── Preview Frame ───────────────────────────────────────────── --}}
    <div class="report-preview-container">
        @php
            // We want to wrap each page in a div for better visual separation in preview
            // But _report_page.blade.php contains both. 
            // Better to split the include logic or just let CSS handle it if we can.
            // Since we can't easily intercept the HTML from @include without regex/buffering,
            // we'll use a slightly different approach: we'll wrap the whole include
            // and use a trick to style the siblings.
        @endphp
        <div class="report-page-wrapper-group contents">
            @include('admin.reports._report_page', ['reportType' => $reportType])
        </div>
    </div>

</div>
@endsection
