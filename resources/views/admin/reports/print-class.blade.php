@extends('layouts.print')

@section('title', 'Cetak Raport Kelas ' . $class->name)

@section('content')

{{-- ══ LOADING SCREEN ══ --}}
<div id="loading-screen" style="
    position: fixed; inset: 0; z-index: 9999;
    background: #f8fafc;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-family: sans-serif;
">
    <div style="text-align: center; max-width: 420px; padding: 2rem;">
        <div style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 0.5rem;">
            Memuat Raport — {{ $class->name }}
        </div>
        <div style="font-size: 10pt; color: #64748b; margin-bottom: 1.5rem;">
            Semester {{ $semester }} — {{ $academicYear }}
        </div>
        <div style="background:#e2e8f0; border-radius:999px; height:10px; margin-bottom:0.75rem; overflow:hidden;">
            <div id="progress-bar" style="
                height:100%; width:0%;
                background: linear-gradient(90deg, #6366f1, #818cf8);
                border-radius:999px;
                transition: width 0.3s ease;
            "></div>
        </div>
        <div id="progress-text" style="font-size:9pt; color:#94a3b8;">Menyiapkan data...</div>
        <div id="current-student" style="font-size:9pt; color:#475569; margin-top:0.25rem; min-height:1.2em;"></div>
    </div>
</div>

{{-- ══ BATCH CONTROL BAR (muncul setelah load selesai) ══ --}}
<div id="batch-bar" style="
    display: none;
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 9998;
    background: #1e293b; color: white;
    padding: 0.75rem 1.5rem;
    align-items: center; justify-content: space-between;
    font-family: sans-serif; font-size: 10pt;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
">
    <div>
        <span style="color:#94a3b8;">Batch </span>
        <span id="batch-current" style="font-weight:bold; color:#818cf8;">1</span>
        <span style="color:#94a3b8;"> dari </span>
        <span id="batch-total" style="font-weight:bold;">1</span>
        <span style="color:#94a3b8; margin-left:1rem;">
            Siswa <span id="batch-range">1–10</span>
        </span>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <button id="btn-prev" onclick="changeBatch(-1)" style="
            background:#334155; color:#cbd5e1;
            border:none; border-radius:6px;
            padding:0.5rem 1rem; font-size:9pt;
            cursor:pointer; display:none;
        ">← Batch Sebelumnya</button>

        <button id="btn-print" onclick="printCurrentBatch()" style="
            background:#6366f1; color:white;
            border:none; border-radius:6px;
            padding:0.5rem 1.25rem;
            font-size:10pt; font-weight:bold;
            cursor:pointer;
        ">🖨️ Cetak Batch Ini</button>

        <button id="btn-next" onclick="changeBatch(1)" style="
            background:#334155; color:#cbd5e1;
            border:none; border-radius:6px;
            padding:0.5rem 1rem; font-size:9pt;
            cursor:pointer; display:none;
        ">Batch Berikutnya →</button>
    </div>
</div>

{{-- ══ REPORT CONTAINER ══ --}}
<div id="report-container" style="display:none;"></div>

@endsection

@section('scripts')
<script>
(function() {
    const BATCH_SIZE    = 30;
    const students      = @json($students);
    const semester      = "{{ $semester }}";
    const academicYear  = "{{ $academicYear }}";
    const reportType    = "{{ $reportType }}";
    const total         = students.length;

    // DOM refs
    const container     = document.getElementById('report-container');
    const loadingScreen = document.getElementById('loading-screen');
    const progressBar   = document.getElementById('progress-bar');
    const progressText  = document.getElementById('progress-text');
    const currentStudentEl = document.getElementById('current-student');
    const batchBar      = document.getElementById('batch-bar');
    const batchCurrent  = document.getElementById('batch-current');
    const batchTotal    = document.getElementById('batch-total');
    const batchRange    = document.getElementById('batch-range');
    const btnPrev       = document.getElementById('btn-prev');
    const btnNext       = document.getElementById('btn-next');

    // State
    let currentBatch = 0;
    let reportWrappers = []; // Array of DOM elements per siswa
    const totalBatches = Math.ceil(total / BATCH_SIZE);

    if (total === 0) {
        loadingScreen.innerHTML = `
            <div style="text-align:center; font-family:sans-serif; color:#9ca3af;">
                <p style="font-size:18pt;">Tidak ada siswa di kelas ini</p>
            </div>`;
        return;
    }

    // ── Fetch semua raport satu per satu ──────────────────────────────
    async function fetchAll() {
        for (let i = 0; i < total; i++) {
            const student = students[i];
            const pct = Math.round((i / total) * 100);

            progressBar.style.width = pct + '%';
            progressText.textContent = `Memuat ${i + 1} dari ${total} siswa...`;
            currentStudentEl.textContent = student.name;

            const wrapper = document.createElement('div');
            wrapper.className = 'report-wrapper';
            wrapper.dataset.studentIndex = i;
            wrapper.style.display = 'none'; // semua hidden dulu

            try {
                // Sesuai route project: /admin/reports/print/{student}
                const url = `/admin/reports/print/${student.id}`
                    + `?semester=${encodeURIComponent(semester)}`
                    + `&academic_year=${encodeURIComponent(academicYear)}`
                    + `&report_type=${encodeURIComponent(reportType)}`;

                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                wrapper.innerHTML = await res.text();

            } catch (err) {
                console.error(`Gagal: ${student.name}`, err);
                wrapper.innerHTML = `
                    <div style="padding:2cm; font-family:sans-serif; color:#ef4444; page-break-after:always;">
                        ⚠️ Gagal memuat raport: <strong>${student.name}</strong>
                    </div>`;
            }

            container.appendChild(wrapper);
            reportWrappers.push(wrapper);
        }

        // Selesai load
        progressBar.style.width = '100%';
        progressText.textContent = 'Semua raport berhasil dimuat!';
        currentStudentEl.textContent = '';

        await new Promise(r => setTimeout(r, 300));

        // Tampilkan UI
        loadingScreen.style.display = 'none';
        container.style.display = 'block';
        batchTotal.textContent = totalBatches;

        // Tampilkan batch pertama
        showBatch(0);
        batchBar.style.display = 'flex';
    }

    // ── Tampilkan batch tertentu ──────────────────────────────────────
    function showBatch(batchIndex) {
        currentBatch = batchIndex;
        const start = batchIndex * BATCH_SIZE;
        const end   = Math.min(start + BATCH_SIZE, total);

        // Sembunyikan semua, tampilkan hanya batch ini
        reportWrappers.forEach((w, i) => {
            w.style.display = (i >= start && i < end) ? 'block' : 'none';
        });

        // Update UI
        batchCurrent.textContent = batchIndex + 1;
        batchRange.textContent   = `${start + 1}–${end}`;

        // Tombol prev/next
        btnPrev.style.display = batchIndex > 0 ? 'inline-block' : 'none';
        btnNext.style.display = batchIndex < totalBatches - 1 ? 'inline-block' : 'none';

        // Scroll ke atas
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Ganti batch ───────────────────────────────────────────────────
    window.changeBatch = function(direction) {
        const next = currentBatch + direction;
        if (next >= 0 && next < totalBatches) {
            showBatch(next);
        }
    };

    // ── Print batch via iframe (hanya batch aktif) ────────────────────
    window.printCurrentBatch = function() {
        const start = currentBatch * BATCH_SIZE;
        const end   = Math.min(start + BATCH_SIZE, total);

        // Kumpulkan HTML batch aktif saja
        let batchHtml = '';
        for (let i = start; i < end; i++) {
            if (reportWrappers[i]) {
                batchHtml += reportWrappers[i].innerHTML;
            }
        }

        // Ambil semua CSS dari halaman ini
        let cssText = '';
        const styleSheets = document.styleSheets;
        for (let i = 0; i < styleSheets.length; i++) {
            try {
                const rules = styleSheets[i].cssRules || styleSheets[i].rules;
                if (rules) {
                    for (let j = 0; j < rules.length; j++) {
                        cssText += rules[j].cssText + '\n';
                    }
                }
            } catch(e) {
                // Skip cross-origin stylesheets
                if (styleSheets[i].href) {
                    cssText += `@import url('${styleSheets[i].href}');\n`;
                }
            }
        }

        // Buat iframe tersembunyi
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed; top:-9999px; left:-9999px; width:210mm; height:297mm; border:none; visibility:hidden;';
        document.body.appendChild(iframe);

        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    ${cssText}
                    /* Reset untuk print */
                    body { margin: 0; padding: 0; background: white; }
                    @page { size: A4; margin: 1cm; }
                </style>
            </head>
            <body>
                ${batchHtml}
            </body>
            </html>
        `);
        doc.close();

        // Tunggu render lalu print
        iframe.onload = function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch(e) {
                console.error('Print error:', e);
            }
            // Hapus iframe setelah print
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 2000);
        };
    };

    // Start
    document.addEventListener('DOMContentLoaded', fetchAll);
})();
</script>

<style>
    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    @media print {
        /* Sembunyikan UI chrome saja — konten dihandle iframe */
        #batch-bar, #loading-screen { display: none !important; }
        /* Sembunyikan report-container dari halaman utama saat print */
        #report-container { display: none !important; }
    }
</style>
@endsection
