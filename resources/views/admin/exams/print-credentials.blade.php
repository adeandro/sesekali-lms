@extends('layouts.app')

@section('title', 'Print Student Credentials - ' . $exam->title)

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Student Credentials - {{ $exam->title }}</h1>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>

        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-800 text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                Total Students: <strong>{{ count($students) }}</strong> | 
                6 cards per F4 page (2x3 grid) | 
                Keep passwords confidential
            </p>
        </div>

        <!-- Student Credential Cards Grid (6 per page - 2x3) -->
        <div style="page-break-after: always;">
            <div class="grid grid-cols-2 gap-3">
        @foreach($students as $index => $data)
            @if($index > 0 && $index % 6 == 0)
                </div>
            </div>
            <div style="page-break-after: always; margin: 0; padding: 0;">
                <div class="grid grid-cols-2 gap-3">
            @endif
            
            <!-- Card -->
            <div class="p-4 bg-white border-2 border-gray-400 rounded-lg shadow" style="font-size: 0.75rem;">
                <!-- Header -->
                <div class="text-center mb-2 border-b-2 border-blue-600 pb-1">
                    <h3 class="text-xs font-bold text-gray-900">CREDENTIAL CARD</h3>
                    <p class="text-xs text-gray-600">{{ $data['exam']->title }}</p>
                </div>

                <!-- Exam Date -->
                <div class="text-center mb-2">
                    <p class="text-xs text-gray-500">{{ $data['exam']->start_time->format('d M Y') }}</p>
                </div>

                <!-- Student ID -->
                <div class="text-center mb-2 bg-blue-100 p-1 rounded">
                    <p class="text-xs text-gray-600 font-semibold">NIS</p>
                    <p class="text-lg font-bold text-blue-600 font-mono">{{ $data['nis'] }}</p>
                </div>

                <!-- Student Name -->
                <div class="mb-2 border-b border-gray-200 pb-1">
                    <p class="text-xs text-gray-600 font-semibold">NAME</p>
                    <p class="text-xs font-bold text-gray-900 truncate">{{ $data['name'] }}</p>
                </div>

                <!-- Class -->
                <div class="mb-3 border-b border-gray-200 pb-1">
                    <p class="text-xs text-gray-600 font-semibold">CLASS</p>
                    <p class="text-xs font-bold text-gray-900">{{ $data['class'] ?? '-' }}</p>
                </div>

                <!-- Login Credentials -->
                <div class="bg-red-50 p-2 rounded-lg border border-red-300 mb-2">
                    <p class="text-xs font-bold text-red-700 text-center mb-1">⚠ CONFIDENTIAL</p>
                    
                    <div class="mb-1">
                        <p class="text-xs text-gray-600 font-semibold">USERNAME</p>
                        <p class="text-center text-xs font-mono font-bold text-gray-900 bg-white rounded px-1 py-0.5">{{ $data['nis'] }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-600 font-semibold">PASSWORD</p>
                        <p class="text-center text-xs font-mono font-bold text-red-600 bg-white rounded px-1 py-0.5">{{ $data['password'] }}</p>
                    </div>
                </div>

                <!-- Quick Instructions -->
                <div class="bg-green-50 p-1.5 rounded-lg border border-green-300 text-center">
                    <p class="text-xs text-green-700 font-bold leading-tight">✓ Use NIS to login • Change password after login</p>
                </div>
            </div>
        @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: 210mm 330mm;
            margin: 0.5cm 0.3cm;
        }

        @media print {
            /* Hide navbar and sidebar during print */
            nav, aside, header, footer, button, .navbar, .sidebar, .btn-print {
                display: none !important;
            }
            
            /* Hide all design elements that aren't part of the cards */
            .mb-6, .mb-4, .bg-yellow-50 {
                display: none !important;
            }
            * {
                overflow: visible !important;
            }
            
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                height: auto !important;
                width: 100% !important;
            }

            body {
                background: white;
                font-family: Arial, sans-serif;
            }

            .max-w-6xl {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            button, .mb-4, .mb-6 {
                display: none !important;
            }

            /* Grid container for cards */
            .grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.2cm;
                width: 100%;
                margin: 0;
                padding: 0;
                page-break-inside: avoid;
            }

            /* Page break container */
            div[style*="page-break-after"] {
                page-break-after: always;
                margin: 0;
                padding: 0;
                display: block;
            }

            /* Card styling */
            .p-4 {
                padding: 0.3cm !important;
                margin: 0 !important;
                font-size: 10pt !important;
                line-height: 1.3;
                break-inside: avoid;
                height: auto;
                min-height: 10cm;
                width: 100%;
            }

            .border-2 {
                border-width: 1px !important;
            }

            /* Text sizes for print */
            .text-xs {
                font-size: 9pt !important;
            }

            .text-sm {
                font-size: 10pt !important;
            }

            .text-lg {
                font-size: 13pt !important;
            }

            .mb-2 {
                margin-bottom: 0.15cm !important;
            }

            .mb-3 {
                margin-bottom: 0.2cm !important;
            }

            .mb-1 {
                margin-bottom: 0.08cm !important;
            }

            .pb-1 {
                padding-bottom: 0.1cm !important;
            }

            /* Section spacing */
            .rounded {
                border-radius: 2px;
            }

            .rounded-lg {
                border-radius: 2px;
            }

            .shadow {
                box-shadow: none;
            }

            .p-1, .p-1\.5, .p-2 {
                padding: 0.1cm !important;
            }

            /* Credential sections */
            .bg-red-50, .bg-blue-100, .bg-green-50 {
                background-color: white !important;
                border: 1px solid #ccc;
            }

            .text-red-600, .text-blue-600 {
                color: #000 !important;
                font-weight: bold;
            }

            .font-mono {
                font-family: 'Courier New', monospace;
                font-size: 9pt;
            }
        }

        @media screen {
            .grid {
                gap: 0.75rem;
            }
        }
    </style>
@endsection
