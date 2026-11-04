<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sertifikat {{ $team->certificate_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #fff;
            color: #1e293b;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            padding: 15mm 18mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
        }

        /* Decorative border */
        .border-outer {
            position: absolute;
            inset: 8mm;
            border: 2.5pt solid #991b1b;
        }
        .border-inner {
            position: absolute;
            inset: 11mm;
            border: 0.8pt solid #fca5a5;
        }

        .content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 10mm 15mm;
            min-height: 180mm;
        }

        .logo-emoji {
            font-size: 36pt;
            line-height: 1;
            margin-bottom: 4mm;
        }

        .school-name {
            font-size: 11pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1pt;
            margin-bottom: 3mm;
        }

        .title-main {
            font-size: 26pt;
            font-weight: bold;
            color: #991b1b;
            text-transform: uppercase;
            letter-spacing: 2pt;
            line-height: 1.1;
            margin-bottom: 2mm;
        }

        .title-sub {
            font-size: 11pt;
            color: #475569;
            font-style: italic;
            margin-bottom: 8mm;
        }

        .given-to {
            font-size: 10pt;
            color: #64748b;
            margin-bottom: 4mm;
        }

        .members-block {
            background: #fef2f2;
            border: 0.5pt solid #fca5a5;
            padding: 5mm 12mm;
            margin-bottom: 6mm;
            min-width: 150mm;
        }

        .member-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.6;
        }

        .info-row {
            font-size: 10pt;
            color: #475569;
            margin-bottom: 2mm;
        }

        .info-row strong { color: #1e293b; }

        .divider {
            width: 80mm;
            border-top: 0.5pt solid #e2e8f0;
            margin: 5mm auto;
        }

        .meta-row {
            display: flex;
            gap: 15mm;
            justify-content: center;
            margin-bottom: 6mm;
        }

        .meta-item {
            text-align: center;
        }

        .meta-label {
            font-size: 7pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .meta-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
        }

        .signature-row {
            display: flex;
            gap: 25mm;
            justify-content: center;
            margin-top: 8mm;
        }

        .signature-block {
            text-align: center;
            width: 55mm;
        }

        .signature-line {
            border-bottom: 0.5pt solid #334155;
            height: 15mm;
            margin-bottom: 2mm;
        }

        .signature-name {
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
        }

        .signature-title {
            font-size: 7.5pt;
            color: #64748b;
        }

        .cert-no {
            position: absolute;
            bottom: 14mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            font-family: 'Courier New', monospace;
        }

        .verify-url {
            font-size: 7pt;
            color: #94a3b8;
            margin-top: 1mm;
        }

        @media print {
            body { background: #fff; }
            @page {
                size: A4 landscape;
                margin: 0;
            }
            .page { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Print toolbar (hidden on print) --}}
<div class="no-print" style="background:#1e293b;padding:10px;text-align:center;position:sticky;top:0;z-index:999;">
    <button onclick="window.print()"
            style="background:#991b1b;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:bold;cursor:pointer;margin-right:10px;">
        🖨️ Cetak Sertifikat
    </button>
    <a href="{{ route('ct.certificate.pdf', $team->certificate_no) }}"
       style="background:#334155;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:bold;cursor:pointer;text-decoration:none;">
        📄 Unduh PDF
    </a>
</div>

<div class="page">
    <div class="border-outer"></div>
    <div class="border-inner"></div>

    <div class="content">
        <div class="logo-emoji">🔐</div>
        <div class="school-name">{{ config('ct.school_name') }}</div>

        <div class="title-main">Sertifikat Penyelesaian</div>
        <div class="title-sub">Operasi Kode Merah: Tim Forensik Digital</div>

        <div class="given-to">Diberikan kepada</div>

        <div class="members-block">
            @foreach($team->member_names as $name)
                <div class="member-name">{{ $name }}</div>
            @endforeach
        </div>

        <div class="info-row">
            Kelas <strong>{{ $team->ctClass->name }}</strong> &nbsp;·&nbsp;
            Kelompok <strong>{{ $team->number }}</strong>
        </div>
        <div class="info-row">
            telah berhasil menyelesaikan tiga tahap <em>Computational Thinking Escape Room</em>
            dan membuka brankas digital pada misi Kode Merah.
        </div>

        <div class="divider"></div>

        <div class="meta-row">
            <div class="meta-item">
                <div class="meta-label">Tanggal</div>
                <div class="meta-value">{{ $team->opened_at->translatedFormat('d F Y') }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Waktu Selesai</div>
                <div class="meta-value">{{ $team->opened_at->format('H:i') }} WIB</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Percobaan Salah</div>
                <div class="meta-value">{{ $wrongCount }}</div>
            </div>
        </div>

        <div class="signature-row">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-name">{{ config('ct.teacher_name') }}</div>
                <div class="signature-title">Guru Informatika</div>
            </div>
        </div>
    </div>

    <div class="cert-no">
        No. {{ $team->certificate_no }}<br>
        <span class="verify-url">Verifikasi: {{ route('ct.verify', $team->certificate_no) }}</span>
    </div>
</div>

</body>
</html>
