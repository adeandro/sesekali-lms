<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kunci Jawaban CT — Kelas {{ $ctClass->name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #1a1a1a; }

    .header { border-bottom: 2pt solid #7c3aed; padding-bottom: 8pt; margin-bottom: 12pt; }
    .header .label { font-size: 7pt; font-weight: bold; color: #7c3aed; text-transform: uppercase; letter-spacing: 1pt; }
    .header h1 { font-size: 14pt; font-weight: bold; color: #1f2937; }
    .header .meta { font-size: 8pt; color: #6b7280; margin-top: 3pt; }

    .warning {
        background: #fef3c7;
        border: 1pt solid #f59e0b;
        border-radius: 4pt;
        padding: 6pt 10pt;
        font-size: 8pt;
        color: #92400e;
        margin-bottom: 12pt;
        font-weight: bold;
        text-align: center;
    }

    table.main {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
    }
    table.main th {
        background: #7c3aed;
        color: white;
        padding: 5pt 6pt;
        text-align: left;
        font-weight: bold;
        font-size: 7.5pt;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
    }
    table.main td {
        padding: 5pt 6pt;
        border-bottom: 0.5pt solid #e5e7eb;
        vertical-align: top;
    }
    table.main tr:nth-child(even) td { background: #f5f3ff; }

    .code { font-family: 'DejaVu Sans Mono', monospace; font-weight: bold; font-size: 13pt; letter-spacing: 3pt; color: #7c3aed; }
    .mono { font-family: 'DejaVu Sans Mono', monospace; }

    .footer { margin-top: 16pt; padding-top: 6pt; border-top: 0.5pt solid #e5e7eb; font-size: 7pt; color: #9ca3af; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <div class="label">RAHASIA — Hanya untuk Guru</div>
    <h1>KUNCI JAWABAN CT ESCAPE ROOM</h1>
    <div class="meta">
        Kelas: <strong>{{ $ctClass->name }}</strong>
        &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
        &nbsp;|&nbsp; {{ $teams->count() }} Kelompok
    </div>
</div>

<div class="warning">
    ⚠ RAHASIA — Jangan ditampilkan kepada siswa. Simpan dengan aman setelah digunakan.
</div>

<table class="main">
    <thead>
        <tr>
            <th style="width:30pt">Tim</th>
            <th style="width:52pt">Kode</th>
            <th style="width:35pt">MM (menit)</th>
            <th style="width:30pt">N</th>
            <th style="width:30pt">F (file)</th>
            <th style="width:50pt">Jam Unduh</th>
            <th style="width:35pt">Shift</th>
            <th>Mapel Utama</th>
            <th>Pesan Asli</th>
        </tr>
    </thead>
    <tbody>
        @foreach($teams as $team)
            @php
                $p    = $team->params;
                $code = $generator->deriveCode($p);
                $key  = $generator->answerKey($p);
            @endphp
            <tr>
                <td style="font-weight:bold; text-align:center">{{ $team->number }}</td>
                <td><span class="code">{{ $code }}</span></td>
                <td class="mono" style="text-align:center">{{ $key['minute'] }}</td>
                <td class="mono" style="text-align:center">{{ $p['n'] }}</td>
                <td class="mono" style="text-align:center">{{ $p['files_count'] }}</td>
                <td class="mono">{{ $p['real_time'] }}</td>
                <td class="mono" style="text-align:center">+{{ $p['shift'] }}</td>
                <td>{{ $p['subjects'][0] ?? '—' }}</td>
                <td class="mono" style="font-size:7.5pt">{{ $key['secret_plain'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    CT Escape Room · Kelas {{ $ctClass->name }} · Kunci Jawaban · {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
