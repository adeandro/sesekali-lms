<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lembar Kasus CT — Tim {{ $ctTeam->number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10pt;
        color: #1a1a1a;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Page layout ── */
    .page {
        width: 100%;
        padding: 1cm 1.2cm;
        page-break-after: always;
    }
    .page:last-child { page-break-after: auto; }

    /* ── Header ── */
    .page-header {
        border-bottom: 2pt solid #1e3a5f;
        padding-bottom: 8pt;
        margin-bottom: 12pt;
    }
    .page-header .label {
        font-size: 7pt;
        font-weight: bold;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1pt;
    }
    .page-header h1 {
        font-size: 15pt;
        font-weight: bold;
        color: #1e3a5f;
        margin-top: 2pt;
    }
    .page-header .meta {
        font-size: 8pt;
        color: #6b7280;
        margin-top: 3pt;
    }
    .team-badge {
        display: inline-block;
        background: #1e3a5f;
        color: white;
        font-size: 9pt;
        font-weight: bold;
        padding: 2pt 10pt;
        border-radius: 20pt;
    }

    /* ── Section ── */
    .section { margin-bottom: 14pt; }
    .section-title {
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1pt;
        color: #1e3a5f;
        border-left: 3pt solid #1e3a5f;
        padding-left: 6pt;
        margin-bottom: 7pt;
    }
    .stage-title {
        font-size: 11pt;
        font-weight: bold;
        color: #1e3a5f;
        margin-bottom: 4pt;
    }
    .instruction {
        font-size: 9pt;
        color: #374151;
        line-height: 1.5;
        margin-bottom: 6pt;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Log table ── */
    .log-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 8.5pt;
        table-layout: fixed;
    }
    .log-table th {
        background: #f3f4f6;
        text-align: left;
        padding: 4pt 6pt;
        font-size: 7.5pt;
        color: #6b7280;
        font-family: 'DejaVu Sans', sans-serif;
        font-weight: bold;
        border-bottom: 1pt solid #e5e7eb;
    }
    .log-table td {
        padding: 3pt 6pt;
        border-bottom: 0.5pt dotted #e5e7eb;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .log-table tr.last-row td {
        border-bottom: 0;
        background: #fff7ed;
    }

    /* ── Cipher examples ── */
    .cipher-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    .cipher-table td {
        padding: 2.5pt 8pt;
        border: 0.5pt solid #e5e7eb;
    }
    .cipher-table .plain { font-family: 'DejaVu Sans Mono', monospace; color: #374151; }
    .cipher-table .arrow { text-align: center; color: #9ca3af; width: 20pt; }
    .cipher-table .enc { font-family: 'DejaVu Sans Mono', monospace; font-weight: bold; color: #1e3a5f; }

    /* ── Folder / file list ── */
    .file-list { list-style: none; }
    .file-list li {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 9pt;
        padding: 2pt 6pt;
        border-bottom: 0.5pt dotted #e5e7eb;
    }
    .file-list li:nth-child(even) { background: #f9fafb; }

    /* ── Answer boxes ── */
    .answer-box {
        display: inline-block;
        border: 1pt solid #d1d5db;
        min-width: 60pt;
        padding: 4pt 8pt;
        font-size: 9pt;
        color: #9ca3af;
        font-style: italic;
        margin-top: 4pt;
    }

    /* ── Code box ── */
    .code-box {
        border: 2pt solid #1e3a5f;
        text-align: center;
        padding: 10pt 20pt;
        margin-top: 8pt;
        border-radius: 4pt;
    }
    .code-box .code-label {
        font-size: 8pt;
        font-weight: bold;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 1pt;
        margin-bottom: 4pt;
    }
    .code-box .code-value {
        font-family: 'DejaVu Sans Mono', monospace;
        font-size: 32pt;
        font-weight: bold;
        letter-spacing: 8pt;
        color: #1e3a5f;
    }
    .code-box .code-hint {
        font-size: 7pt;
        color: #9ca3af;
        margin-top: 4pt;
    }

    /* ── Info box ── */
    .info-box {
        background: #eff6ff;
        border: 0.5pt solid #bfdbfe;
        border-radius: 4pt;
        padding: 8pt 10pt;
        font-size: 8.5pt;
        color: #1e40af;
        margin-bottom: 10pt;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* ── Footer ── */
    .page-footer {
        margin-top: 16pt;
        padding-top: 6pt;
        border-top: 0.5pt solid #e5e7eb;
        font-size: 7pt;
        color: #9ca3af;
        text-align: center;
    }

    /* ── Two column via table (dompdf-safe) ── */
    .two-col { display: table; width: 100%; table-layout: fixed; }
    .col-left { display: table-cell; width: 58%; padding-right: 10pt; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
    .col-right { display: table-cell; width: 42%; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }

    .member-lines { margin-top: 6pt; }
    .member-line {
        border-bottom: 0.5pt solid #d1d5db;
        margin-bottom: 12pt;
        padding-bottom: 2pt;
        font-size: 9pt;
        color: #9ca3af;
    }
    .member-line::before {
        content: attr(data-label);
        font-size: 7pt;
        color: #d1d5db;
        display: block;
        margin-bottom: 2pt;
    }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════
     PAGE 1 — Narasi + Tahap 1
     ═══════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Header --}}
    <div class="page-header">
        <div class="label">CT Escape Room — Kode Merah: Siapa yang Bocorkan Soal?</div>
        <h1>LEMBAR KASUS KELOMPOK</h1>
        <div class="meta">
            Kelas: <strong>{{ $ctTeam->ctClass->name }}</strong> &nbsp;|&nbsp;
            <span class="team-badge">KELOMPOK {{ $ctTeam->number }}</span>
            &nbsp;|&nbsp; Halaman 1 dari 2
        </div>
    </div>

    {{-- Member names --}}
    <div class="section">
        <div class="section-title">Identitas Kelompok</div>
        <div class="member-lines">
            @for($i = 0; $i < 4; $i++)
            <div class="member-line" data-label="Anggota {{ $i+1 }}">{{ $ctTeam->member_names[$i] ?? '' }}</div>
            @endfor
        </div>
    </div>

    {{-- Latar belakang --}}
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
            <strong>{{ config('ct.public_url', url('/ct')) }}/kelas/{{ urlencode($ctTeam->ctClass->name) }}</strong>
        </div>
    </div>

    {{-- Tahap 1 --}}
    <div class="section">
        <div class="stage-title">🔎 Tahap 1 — Analisis Log Server</div>
        <div class="instruction">
            Perhatikan log aktivitas server di bawah. Temukan <strong>satu entri mencurigakan</strong>:
            akun lama yang mengunduh soal ujian di luar jam kebiasaan (tengah malam / subuh).
            Catat <strong>2 digit menit</strong> dari jam unduhan tersebut.
        </div>

        {{-- Log table --}}
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width:48pt">JAM</th>
                    <th style="width:68pt">AKUN</th>
                    <th style="width:36pt">AKSI</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                @php $logCount = count($log); @endphp
                @foreach($log as $i => $line)
                <tr class="{{ $i === $logCount - 1 ? 'last-row' : '' }}">
                    @php $parts = explode(' | ', $line, 4); @endphp
                    <td><strong>{{ $parts[0] ?? '' }}</strong></td>
                    <td>{{ trim($parts[1] ?? '') }}</td>
                    <td>{{ trim($parts[2] ?? '') }}</td>
                    <td>{{ trim($parts[3] ?? '') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:10pt;">
            <strong style="font-size:9pt">Jawaban Tahap 1 — 2 digit menit dari jam unduhan mencurigakan:</strong>
            <div class="answer-box">__ __</div>
            <span style="font-size:8pt; color:#6b7280; margin-left:8pt">(tulis di sini, sebut <strong>MM</strong>)</span>
        </div>
    </div>

    <div class="page-footer">
        CT Escape Room · Kelas {{ $ctTeam->ctClass->name }} · Kelompok {{ $ctTeam->number }} · Hal. 1/2
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     PAGE 2 — Tahap 2 + Tahap 3 + Kode
     ═══════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Header --}}
    <div class="page-header">
        <div class="label">CT Escape Room — Kode Merah: Siapa yang Bocorkan Soal?</div>
        <h1>LEMBAR KASUS KELOMPOK — LANJUTAN</h1>
        <div class="meta">
            Kelas: <strong>{{ $ctTeam->ctClass->name }}</strong> &nbsp;|&nbsp;
            <span class="team-badge">KELOMPOK {{ $ctTeam->number }}</span>
            &nbsp;|&nbsp; Halaman 2 dari 2
        </div>
    </div>

    <div class="two-col">
        <div class="col-left">

            {{-- Tahap 2 --}}
            <div class="section">
                <div class="stage-title">🔐 Tahap 2 — Dekripsi Pesan</div>
                <div class="instruction">
                    Dalam folder cadangan ditemukan pesan terenkripsi menggunakan <strong>sandi Caesar</strong>.
                    Gunakan tabel contoh di bawah untuk mencari pola pergeseran, lalu dekripsi pesan berikut.
                    Temukan <strong>angka</strong> yang tersembunyi dalam pesan asli.
                </div>

                {{-- Cipher examples --}}
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
                    <span style="font-family:'DejaVu Sans Mono',monospace; font-size:11pt; font-weight:bold; letter-spacing:1pt; color:#1e3a5f">
                        {{ $cipher['secret_cipher'] }}
                    </span>
                </div>

                <div>
                    <strong style="font-size:9pt">Pesan asli (dekripsi):</strong>
                    <div class="answer-box" style="min-width:160pt; display:block; margin-top:4pt">__________________________</div>
                </div>

                <div style="margin-top:8pt;">
                    <strong style="font-size:9pt">Jawaban Tahap 2 — Angka dalam pesan asli:</strong>
                    <div class="answer-box" style="min-width:40pt">__</div>
                    <span style="font-size:8pt; color:#6b7280; margin-left:8pt">(sebut <strong>N</strong>)</span>
                </div>
            </div>

        </div>
        <div class="col-right">

            {{-- Tahap 3 --}}
            <div class="section">
                <div class="stage-title">📁 Tahap 3 — Hitung File</div>
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
                    <strong style="font-size:9pt">Jawaban Tahap 3 — Jumlah file soal:</strong>
                    <div class="answer-box" style="min-width:30pt">__</div>
                    <span style="font-size:8pt; color:#6b7280; margin-left:8pt">(sebut <strong>F</strong>)</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Kode final --}}
    <div class="section" style="margin-top:4pt">
        <div class="stage-title">🔑 Kode Brankas</div>
        <div class="instruction">
            Gabungkan jawaban ketiga tahap menjadi kode 4 digit:<br>
            <strong>Kode = MM + N + F</strong>
            <span style="font-size:8pt; color:#6b7280">&nbsp;(misalnya: MM=47, N=5, F=4 → kode 4754)</span>
        </div>
        <div class="code-box">
            <div class="code-label">Kode yang akan Anda masukkan</div>
            <div class="code-value">_ _ _ _</div>
            <div class="code-hint">MM &nbsp;&nbsp; N &nbsp;&nbsp; F</div>
        </div>
    </div>

    {{-- URL --}}
    <div class="info-box" style="margin-top:8pt; text-align:center; font-size:9pt;">
        Masukkan kode di: <strong>{{ config('ct.public_url', url('/ct')) }}</strong>
        &nbsp;→ Pilih kelas <strong>{{ $ctTeam->ctClass->name }}</strong>
        → Kelompok <strong>{{ $ctTeam->number }}</strong>
    </div>

    <div class="page-footer">
        CT Escape Room · Kelas {{ $ctTeam->ctClass->name }} · Kelompok {{ $ctTeam->number }} · Hal. 2/2
    </div>
</div>

</body>
</html>
