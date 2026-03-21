<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Ekstrakurikuler</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; text-transform: uppercase; }
        .header h1 { margin: 0; font-size: 16px; margin-bottom: 5px; }
        .header h2 { margin: 0; font-size: 12px; color: #666; }
        .info { margin-bottom: 15px; width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info td { padding: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f4f4f4; text-transform: uppercase; font-weight: bold; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { padding: 2px 5px; border-radius: 3px; font-weight: bold; }
        .footer { margin-top: 50px; }
        .signature { width: 250px; float: right; text-align: center; }
        .page-break { page-break-after: always; }
        .section-title { font-weight: bold; font-size: 11px; margin-bottom: 10px; text-transform: uppercase; border-left: 4px solid #333; padding-left: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KEHADIRAN & JURNAL KEGIATAN</h1>
        <h2>EKSTRAKURIKULER: {{ $extracurricular->name }}</h2>
    </div>

    <table class="info" style="border:none;">
        <tr style="border:none;">
            <td style="border:none; width: 120px;">Semester</td>
            <td style="border:none;">: {{ $semester }} ({{ $semester == 1 ? 'Ganjil' : 'Genap' }})</td>
            <td style="border:none; width: 120px;">Sekolah</td>
            <td style="border:none;">: {{ $configs['school_name'] ?? 'SMK Sesekali' }}</td>
        </tr>
        <tr style="border:none;">
            <td style="border:none;">Tahun Ajaran</td>
            <td style="border:none;">: {{ $academicYear }}</td>
            <td style="border:none;">Total Pertemuan</td>
            <td style="border:none;">: {{ $sessions->count() }} Kali</td>
        </tr>
    </table>

    <div class="section-title">Rekap Kehadiran Siswa</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="30">No</th>
                <th>Nama Siswa</th>
                <th class="text-center">NIS</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">H</th>
                <th class="text-center">I</th>
                <th class="text-center">S</th>
                <th class="text-center">A</th>
                <th class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recap as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['student']->name }}</td>
                    <td class="text-center">{{ $row['student']->nis ?? '-' }}</td>
                    <td class="text-center">{{ $row['student']->classroom->name ?? '-' }}</td>
                    <td class="text-center">{{ $row['hadir'] }}</td>
                    <td class="text-center">{{ $row['izin'] }}</td>
                    <td class="text-center">{{ $row['sakit'] }}</td>
                    <td class="text-center">{{ $row['alfa'] }}</td>
                    <td class="text-center"><b>{{ $row['pct_hadir'] }}%</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Jurnal Pertemuan</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="30">No</th>
                <th width="100">Tanggal</th>
                <th>Materi / Topik Pembahasan</th>
                <th>Catatan / Keterangan</th>
                <th class="text-center" width="60">Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $index => $session)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $session->date->translatedFormat('d F Y') }}</td>
                    <td>{{ $session->topic }}</td>
                    <td>{{ $session->notes ?? '-' }}</td>
                    <td class="text-center">{{ $session->studentAttendances()->where('status', 'hadir')->count() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Rekap Kehadiran Pembina</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="30">No</th>
                <th>Nama Pembina</th>
                <th class="text-center" width="80">Total Pertemuan</th>
                <th class="text-center" width="60">Hadir</th>
                <th class="text-center" width="80">Tidak Hadir</th>
                <th class="text-center" width="60">% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coachRecap as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['hadir'] }}</td>
                    <td class="text-center">{{ $row['tidak_hadir'] }}</td>
                    <td class="text-center"><b>{{ $row['pct_hadir'] }}%</b></td>
                </tr>
            @endforeach
            @if(empty($coachRecap))
                <tr>
                    <td colspan="6" class="text-center" 
                        style="padding: 16px; color: #999;">
                        Belum ada data kehadiran pembina
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
    @php
        $principal = \App\Models\User::where('role', 'principal')->first();
        $principalName = null;
        if ($principal) {
            $parts = [];
            if ($principal->title_ahead) $parts[] = trim($principal->title_ahead);
            $parts[] = trim($principal->name);
            $principalName = implode(' ', $parts);
            if ($principal->title_behind) 
                $principalName .= ', ' . trim($principal->title_behind);
        }
        $principalNip = null;
        if ($principal) {
            if (!empty($principal->nip)) $principalNip = 'NIP. ' . $principal->nip;
            elseif (!empty($principal->niy)) $principalNip = 'NIY. ' . $principal->niy;
        }
    @endphp

    <div class="signature">
        <p>{{ $configs['school_village'] ?? $configs['school_city'] ?? '' }}, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
        <p style="margin-bottom: 20px;">Kepala Sekolah,</p>
        @if($principal && $principal->signature && $principal->is_signature_active)
            <img src="{{ storage_path('app/public/signatures/' . $principal->signature) }}" 
                 style="height: 80px; object-fit: contain; margin-bottom: 4px;">
        @endif
        <p><b>{{ $principalName ?? '__________________________' }}</b></p>
        @if($principalNip)
            <p>{{ $principalNip }}</p>
        @endif
    </div>
</div>
</body>
</html>
