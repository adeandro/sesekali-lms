<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Raport')</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ── Print Layout — Khusus A4 ── */
        * {
            box-sizing: border-box !important;
        }
        
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }

        @media print {
            html, body {
                width: 210mm !important;
                height: auto !important;
                background: white !important;
                overflow: visible !important;
            }
            @page {
                size: A4;
                margin: 0;
            }
            .report-page {
                width: 210mm !important;
                min-height: 270mm !important; /* min-height, bukan height fixed */
                padding: 1.5cm 1.5cm 1.5cm 2cm !important;
                margin: 0 !important;
                box-shadow: none !important;
                page-break-after: always;
                break-after: page;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                position: relative;
                overflow: visible !important;
            }
            .report-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .no-print {
                display: none !important;
            }
        }

        @media screen {
            body {
                padding: 24px;
                background: #e5e7eb;
            }
            .report-page {
                width: 210mm;
                min-height: 297mm;
                padding: 1.5cm 1.5cm 1.5cm 2cm;
                margin: 0 auto 24px auto;
                background: white;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                position: relative;
                overflow: hidden;
            }
            .report-page:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    @yield('content')

    @yield('scripts')
</body>
</html>
