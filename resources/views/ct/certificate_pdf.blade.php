<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PDF Sertifikat {{ $team->certificate_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #fff;
            color: #1e293b;
        }
        .wrapper {
            border: 2pt solid #991b1b;
            padding: 2mm;
        }
        .wrapper-inner {
            border: 0.5pt solid #fca5a5;
            padding: 10mm 14mm;
            text-align: center;
        }
        .school-name {
            font-size: 9pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1pt;
            margin-bottom: 3mm;
        }
        .title-main {
            font-size: 22pt;
            font-weight: bold;
            color: #991b1b;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            margin-bottom: 1mm;
        }
        .title-sub {
            font-size: 10pt;
            color: #475569;
            font-style: italic;
            margin-bottom: 6mm;
        }
        .given-to {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 3mm;
        }
        .members-block {
            background: #fff5f5;
            border: 0.5pt solid #fca5a5;
            padding: 4mm 10mm;
            margin: 0 auto 5mm;
            display: inline-block;
            min-width: 120mm;
        }
        .member-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.6;
        }
        .info-line {
            font-size: 9pt;
            color: #475569;
            margin-bottom: 1.5mm;
        }
        .info-line strong { color: #1e293b; }
        .divider {
            border-top: 0.5pt solid #e2e8f0;
            margin: 4mm auto;
            width: 100mm;
        }
        .meta-table {
            margin: 0 auto 5mm;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1mm 8mm;
            text-align: center;
            vertical-align: top;
        }
        .meta-label {
            font-size: 7pt;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .meta-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
        }
        .sig-block {
            margin: 6mm auto 0;
            width: 55mm;
            text-align: center;
        }
        .sig-line {
            border-bottom: 0.5pt solid #334155;
            height: 12mm;
            margin-bottom: 1.5mm;
        }
        .sig-name {
            font-size: 9pt;
            font-weight: bold;
        }
        .sig-title {
            font-size: 7.5pt;
            color: #64748b;
        }
        .footer-cert {
            margin-top: 5mm;
            font-size: 7pt;
            color: #94a3b8;
            font-family: 'DejaVu Sans Mono', monospace;
        }
    </style>
</head>
<body>
<div class="wrapper">
<div class="wrapper-inner">
    <div class="school-name">{{ config('ct.school_name') }}</div>
    <div class="title-main">Sertifikat Penyelesaian</div>
    <div class="title-sub">Operasi Kode Merah: Tim Forensik Digital</div>

    <div class="given-to">Diberikan kepada</div>

    <div class="members-block">
        @foreach($team->member_names as $name)
            <div class="member-name">{{ $name }}</div>
        @endforeach
    </div>

    <div class="info-line">
        Kelas <strong>{{ $team->ctClass->name }}</strong> &nbsp;&bull;&nbsp;
        Kelompok <strong>{{ $team->number }}</strong>
    </div>
    <div class="info-line">
        telah berhasil menyelesaikan tiga tahap Computational Thinking Escape Room dan membuka brankas digital pada misi Kode Merah.
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Tanggal</div>
                <div class="meta-value">{{ $team->opened_at->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="meta-label">Waktu Selesai</div>
                <div class="meta-value">{{ $team->opened_at->format('H:i') }} WIB</div>
            </td>
            <td>
                <div class="meta-label">Percobaan Salah</div>
                <div class="meta-value">{{ $wrongCount }}</div>
            </td>
        </tr>
    </table>

    <div class="sig-block">
        <div class="sig-line"></div>
        <div class="sig-name">{{ config('ct.teacher_name') }}</div>
        <div class="sig-title">Guru Informatika</div>
    </div>

    <div class="footer-cert">
        No. {{ $team->certificate_no }} &nbsp;&bull;&nbsp;
        Verifikasi: {{ route('ct.verify', $team->certificate_no) }}
    </div>
</div>
</div>
</body>
</html>
