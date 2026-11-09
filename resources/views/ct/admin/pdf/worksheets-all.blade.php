<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Semua Lembar Kasus — Kelas {{ $ctClass->name }}</title>
<style>
    * {
        box-sizing: border-box;
    }
    @page {
        size: A4 portrait;
        margin: 12mm 15mm 12mm 15mm;
    }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        color: #1a1a1a;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Page layout ── */
    .page {
        width: 100%;
    }
    .page-break {
        page-break-after: always;
        height: 0;
        line-height: 0;
        font-size: 0;
    }

    /* ── Header ── */
    .page-header {
        border-bottom: 2pt solid #1e3a5f;
        padding-bottom: 6pt;
        margin-bottom: 8pt;
    }
    .page-header .label {
        font-size: 7pt;
        font-weight: bold;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1pt;
    }
    .page-header h1 {
        font-size: 14pt;
        font-weight: bold;
        color: #1e3a5f;
        margin-top: 2pt;
    }
    .page-header .meta {
        font-size: 8pt;
        color: #6b7280;
        margin-top: 2pt;
    }
    .team-badge {
        display: inline-block;
        background: #1e3a5f;
        color: white;
        font-size: 8.5pt;
        font-weight: bold;
        padding: 1.5pt 8pt;
        border-radius: 20pt;
    }

    /* ── Section ── */
    .section { margin-bottom: 8pt; }
    .section-title {
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1pt;
        color: #1e3a5f;
        border-left: 3pt solid #1e3a5f;
        padding-left: 5pt;
        margin-bottom: 5pt;
    }
    .stage-title {
        font-size: 10pt;
        font-weight: bold;
        color: #1e3a5f;
        margin-bottom: 3pt;
    }
    .instruction {
        font-size: 8.5pt;
        color: #374151;
        line-height: 1.45;
        margin-bottom: 5pt;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Log table ── */
    .log-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 8pt;
        table-layout: fixed;
    }
    .log-table th {
        background: #f3f4f6;
        text-align: left;
        padding: 3pt 5pt;
        font-size: 7pt;
        color: #6b7280;
        font-family: 'DejaVu Sans', sans-serif;
        font-weight: bold;
        border-bottom: 1pt solid #e5e7eb;
    }
    .log-table td {
        padding: 2.5pt 5pt;
        border-bottom: 0.5pt dotted #e5e7eb;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .log-table tr.last-row td {
        border-bottom: 0;
    }

    /* ── Cipher examples ── */
    .cipher-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
    }
    .cipher-table td {
        padding: 2pt 6pt;
        border: 0.5pt solid #e5e7eb;
    }
    .cipher-table .plain { font-family: 'DejaVu Sans Mono', monospace; color: #374151; }
    .cipher-table .arrow { text-align: center; color: #9ca3af; width: 16pt; }
    .cipher-table .enc { font-family: 'DejaVu Sans Mono', monospace; font-weight: bold; color: #1e3a5f; }

    /* ── Folder / file list ── */
    .file-list { list-style: none; }
    .file-list li {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 8.5pt;
        padding: 1.5pt 5pt;
        border-bottom: 0.5pt dotted #e5e7eb;
    }
    .file-list li:nth-child(even) { background: #f9fafb; }

    /* ── Answer boxes ── */
    .answer-box {
        display: inline-block;
        border: 1pt solid #d1d5db;
        min-width: 50pt;
        padding: 3pt 6pt;
        font-size: 8.5pt;
        color: #9ca3af;
        font-style: italic;
        margin-top: 3pt;
    }

    /* ── Code box ── */
    .code-box {
        border: 2pt solid #1e3a5f;
        text-align: center;
        padding: 8pt 16pt;
        margin-top: 6pt;
        border-radius: 4pt;
    }
    .code-box .code-label {
        font-size: 7.5pt;
        font-weight: bold;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1pt;
        margin-bottom: 3pt;
    }
    .code-box .code-value {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 28pt;
        font-weight: bold;
        letter-spacing: 6pt;
        color: #1e3a5f;
    }
    .code-box .code-hint {
        font-size: 7pt;
        color: #9ca3af;
        margin-top: 3pt;
    }

    /* ── Info box ── */
    .info-box {
        background: #eff6ff;
        border: 0.5pt solid #bfdbfe;
        border-radius: 4pt;
        padding: 6pt 8pt;
        font-size: 8pt;
        color: #1e40af;
        margin-bottom: 7pt;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Footer ── */
    .page-footer {
        margin-top: 10pt;
        padding-top: 4pt;
        border-top: 0.5pt solid #e5e7eb;
        font-size: 7pt;
        color: #9ca3af;
        text-align: center;
    }

    /* ── Two column via table (dompdf-safe) ── */
    .two-col { display: table; width: 100%; table-layout: fixed; }
    .col-left { display: table-cell; width: 50%; padding-right: 8pt; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
    .col-right { display: table-cell; width: 50%; padding-left: 8pt; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }

    .member-lines { margin-top: 4pt; }
    .member-line {
        border-bottom: 0.5pt solid #d1d5db;
        margin-bottom: 8pt;
        padding-bottom: 2pt;
        font-size: 8.5pt;
        color: #9ca3af;
    }
    .member-line::before {
        content: attr(data-label);
        font-size: 7pt;
        color: #d1d5db;
        display: block;
        margin-bottom: 1pt;
    }
</style>
</head>
<body>

@foreach($teamsData as $td)
    @php
        $ctTeam      = $td['team'];
        $log         = $td['log'];
        $cipher      = $td['cipher'];
        $folderFiles = $td['folderFiles'];
    @endphp

    {{-- ═══════════════════════════════════════════════════════
         PAGE A: Narasi + Tahap 1
         ═══════════════════════════════════════════════════════ --}}
    <div class="page">
        <div class="page-header">
            <div class="label">CT Escape Room — Kode Merah: Siapa yang Bocorkan Soal?</div>
            <h1>LEMBAR KASUS KELOMPOK</h1>
            <div class="meta">
                Kelas: <strong>{{ $ctClass->name }}</strong> &nbsp;|&nbsp;
                <span class="team-badge">KELOMPOK {{ $ctTeam->number }}</span>
                &nbsp;|&nbsp; Halaman 1 dari 2
            </div>
        </div>

        <div class="section">
            <div class="section-title">Identitas Kelompok</div>
            <div class="member-lines">
                @for($i = 0; $i < 4; $i++)
                <div class="member-line" data-label="Anggota {{ $i+1 }}">{{ $ctTeam->member_names[$i] ?? '' }}</div>
                @endfor
            </div>
        </div>

        <div class="section">
            <div class="section-title">Latar Belakang Kasus</div>
            <div class="instruction">
                Panitia ujian sekolah menemukan bahwa <strong>soal ujian bocor</strong> pada malam sebelum pelaksanaan.
                Investigasi digital diarahkan pada server log sekolah. Sebagai tim forensik digital,
                kalian harus menemukan siapa pelaku, kapan, dan berapa file yang diunduh — lalu masukkan
                kode ke brankas digital untuk membuka bukti akhir.
            </div>
            <div class="info-box">
                <strong>Cara kerja:</strong> Selesaikan Tahap 1, 2, dan 3 pada lembar ini. Kombinasikan jawaban
                ketiga tahap menjadi <strong>kode 4 digit</strong>. Masukkan kode ke
                <strong>{{ config('ct.public_url', url('/ct')) }}/kelas/{{ urlencode($ctClass->name) }}</strong>
            </div>
        </div>

        <div class="section">
            <div class="stage-title">Tahap 1 — Analisis Log Server</div>
            <div class="instruction">
                Server sekolah mencatat aktivitas dari beberapa sumber, jadi urutan baris tidak selalu menurut jam. Format: jam | akun | aksi | berkas atau status. Sebagian besar baris hanyalah noise. Temukan satu baris milik pelaku.
            </div>
            <table class="log-table">
                <thead><tr><th style="width:14%">JAM</th><th style="width:24%">AKUN</th><th style="width:14%">AKSI</th><th style="width:48%">DETAIL</th></tr></thead>
                <tbody>
                    @php $logCount = count($log); @endphp
                    @foreach($log as $i => $line)
                    <tr class="{{ $i === $logCount-1 ? 'last-row' : '' }}">
                        @php $parts = explode(' | ', $line, 4); @endphp
                        <td><strong>{{ $parts[0] ?? '' }}</strong></td>
                        <td>{{ trim($parts[1] ?? '') }}</td>
                        <td>{{ trim($parts[2] ?? '') }}</td>
                        <td>{{ trim($parts[3] ?? '') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:8pt;">
                <strong style="font-size:8.5pt">Jawaban Tahap 1 — 2 digit menit:</strong>
                <div class="answer-box">__ __</div>
                <span style="font-size:8pt; color:#6b7280; margin-left:6pt">(sebut <strong>MM</strong>)</span>
            </div>
        </div>

        <div class="page-footer">CT Escape Room · Kelas {{ $ctClass->name }} · Kelompok {{ $ctTeam->number }} · Hal. 1/2</div>
    </div>

    <div class="page-break"></div>

    {{-- ═══════════════════════════════════════════════════════
         PAGE B: Tahap 2 + 3 + Kode
         ═══════════════════════════════════════════════════════ --}}
    <div class="page">
        <div class="page-header">
            <div class="label">CT Escape Room — Kode Merah: Siapa yang Bocorkan Soal?</div>
            <h1>LEMBAR KASUS KELOMPOK — LANJUTAN</h1>
            <div class="meta">
                Kelas: <strong>{{ $ctClass->name }}</strong> &nbsp;|&nbsp;
                <span class="team-badge">KELOMPOK {{ $ctTeam->number }}</span>
                &nbsp;|&nbsp; Halaman 2 dari 2
            </div>
        </div>

        <div class="two-col">
            <div class="col-left">
                <div class="section">
                    <div class="stage-title">Tahap 2 — Dekripsi Pesan</div>
                    <div class="instruction">
                        Dalam folder cadangan ditemukan pesan terenkripsi menggunakan <strong>sandi Caesar</strong>.
                        Gunakan tabel contoh di bawah untuk mencari pola pergeseran, lalu dekripsi pesan berikut.
                        Temukan <strong>angka</strong> yang tersembunyi dalam pesan asli.
                    </div>
                    <div class="section-title" style="font-size:7.5pt; margin-bottom:4pt">Contoh huruf yang sudah terenkripsi:</div>
                    <table class="cipher-table" style="width:auto; margin-bottom:8pt">
                        <tbody>
                            @foreach($cipher['examples'] as $ex)
                            <tr>
                                <td class="plain">{{ $ex['plain'] }}</td>
                                <td class="arrow">→</td>
                                <td class="enc">{{ $ex['cipher'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="margin-bottom:6pt; font-size:9pt">
                        <strong>Pesan terenkripsi:</strong><br>
                        <span style="font-family:'DejaVu Sans Mono',monospace; font-size:11pt; font-weight:bold; letter-spacing:1pt; color:#1e3a5f">{{ $cipher['secret_cipher'] }}</span>
                    </div>
                    <div>
                        <strong style="font-size:8.5pt">Pesan asli (dekripsi):</strong>
                        <div class="answer-box" style="min-width:160pt; display:block; margin-top:4pt">__________________________</div>
                    </div>
                    <div style="margin-top:8pt;">
                        <strong style="font-size:8.5pt">Jawaban Tahap 2 — Angka:</strong>
                        <div class="answer-box" style="min-width:30pt">__</div>
                        <span style="font-size:8pt; color:#6b7280; margin-left:6pt">(sebut <strong>N</strong>)</span>
                    </div>
                </div>
            </div>
            <div class="col-right">
                <div class="section">
                    <div class="stage-title">Tahap 3 — Hitung File</div>
                    <div class="instruction">
                        Berikut isi folder bukti yang diunduh tersangka.
                        Hitung hanya file soal ujian (<code>soal_*.pdf</code>). Abaikan file lain.
                    </div>
                    <ul class="file-list">
                        @foreach($folderFiles as $f)
                        <li>{{ $f }}</li>
                        @endforeach
                    </ul>
                    <div style="margin-top:8pt;">
                        <strong style="font-size:8.5pt">Jawaban Tahap 3 — File:</strong>
                        <div class="answer-box" style="min-width:30pt">__</div>
                        <span style="font-size:8pt; color:#6b7280; margin-left:6pt">(sebut <strong>F</strong>)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section" style="margin-top:4pt">
            <div class="stage-title">Kode Brankas</div>
            <div class="instruction">
                Gabungkan jawaban ketiga tahap menjadi kode 4 digit:<br>
                <strong>Kode = MM + N + F</strong>
                <span style="font-size:8pt; color:#6b7280">&nbsp;(misalnya: MM=47, N=5, F=4 → kode 4754)</span>
            </div>
            <div class="code-box">
                <div class="code-label">Kode yang akan Anda masukkan</div>
                <div class="code-value">_ _ _ _</div>
                <div class="code-hint">MM &nbsp; N &nbsp; F</div>
            </div>
        </div>

        <div class="info-box" style="margin-top:6pt; text-align:center; font-size:8pt;">
            Masukkan kode di: <strong>{{ config('ct.public_url', url('/ct')) }}/kelas/{{ urlencode($ctClass->name) }}</strong>
            &nbsp;→ Pilih Kelompok <strong>{{ $ctTeam->number }}</strong>
        </div>

        <div class="page-footer">CT Escape Room · Kelas {{ $ctClass->name }} · Kelompok {{ $ctTeam->number }} · Hal. 2/2</div>
    </div>

    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

@endforeach

</body>
</html>
