<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi & Pengumuman — {{ $schoolName }}</title>
    <meta name="description" content="Halaman informasi dan pengumuman resmi dari {{ $schoolName }}.">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #f1f5f9;
            padding: 0;
        }

        /* ── Animated Background ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.10) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 720px;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem;
        }

        /* ── Top Bar ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            color: rgba(165,180,252,0.85);
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.25);
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .back-btn:hover {
            background: rgba(99,102,241,0.22);
            color: #a5b4fc;
            transform: translateY(-1px);
        }

        .page-badge {
            font-size: 10px;
            font-weight: 800;
            color: #a5b4fc;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: rgba(99,102,241,0.15);
            padding: 5px 10px;
            border-radius: 8px;
        }

        /* ── Header ── */
        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .page-header h1 {
            font-size: clamp(22px, 5vw, 32px);
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 13px;
            color: rgba(165,180,252,0.7);
        }

        .header-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            color: #a5b4fc;
            background: rgba(165,180,252,0.08);
            border: 1px solid rgba(165,180,252,0.15);
            padding: 6px 14px;
            border-radius: 99px;
            margin-bottom: 16px;
        }

        /* ── Cards ── */
        .ann-list { display: flex; flex-direction: column; gap: 14px; }

        .ann-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 18px;
            padding: 20px 22px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            transition: transform 0.2s, background 0.2s;
            animation: card-in 0.4s ease both;
        }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .ann-card:hover {
            background: rgba(255,255,255,0.09);
            transform: translateY(-2px);
        }

        .ann-card.urgent {
            background: linear-gradient(135deg, rgba(190,18,60,0.18), rgba(159,18,57,0.12));
            border-color: rgba(190,18,60,0.3);
        }
        .ann-card.warning {
            background: linear-gradient(135deg, rgba(217,119,6,0.15), rgba(180,83,9,0.10));
            border-color: rgba(217,119,6,0.25);
        }
        .ann-card.info {
            background: rgba(255,255,255,0.06);
            border-color: rgba(99,102,241,0.2);
        }

        .ann-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .ann-icon.urgent  { background: rgba(190,18,60,0.25); }
        .ann-icon.warning { background: rgba(217,119,6,0.20); }
        .ann-icon.info    { background: rgba(99,102,241,0.18); }

        .ann-body { flex: 1; min-width: 0; }

        .ann-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .ann-type-badge {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 3px 9px;
            border-radius: 6px;
        }
        .ann-type-badge.urgent  { background: rgba(190,18,60,0.25); color: #fca5a5; }
        .ann-type-badge.warning { background: rgba(217,119,6,0.20);  color: #fcd34d; }
        .ann-type-badge.info    { background: rgba(99,102,241,0.20); color: #a5b4fc; }

        .ann-date {
            font-size: 10px;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
        }

        .ann-title {
            font-size: 15px;
            font-weight: 800;
            color: #f1f5f9;
            line-height: 1.35;
            margin-bottom: 5px;
        }
        .ann-card.urgent  .ann-title { color: #fecaca; }
        .ann-card.warning .ann-title { color: #fef3c7; }

        .ann-content {
            font-size: 13px;
            color: rgba(248,250,252,0.65);
            line-height: 1.6;
        }

        .ann-expires {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            color: rgba(252,165,165,0.7);
            font-weight: 600;
            margin-top: 8px;
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-icon {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.20);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        .empty-state h3 {
            font-size: 17px;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 8px;
        }
        .empty-state p {
            font-size: 13px;
            color: rgba(165,180,252,0.6);
        }

        /* ── Footer ── */
        .page-footer {
            text-align: center;
            margin-top: 3rem;
            font-size: 11px;
            color: rgba(165,180,252,0.35);
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">

    {{-- Top Bar --}}
    <div class="topbar">
        <a href="{{ route('login') }}" class="back-btn">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Kembali ke Login
        </a>
        <span class="page-badge">
            <i class="fas fa-bullhorn" style="margin-right:4px"></i> Publik
        </span>
    </div>

    {{-- Header --}}
    <div class="page-header">
        <div class="header-pill">
            <span style="width:7px;height:7px;border-radius:50%;background:#34d399;box-shadow:0 0 6px #34d399;display:inline-block;"></span>
            Informasi Resmi Sekolah
        </div>
        <h1>📢 Pengumuman &amp; Info</h1>
        <p>{{ $schoolName }} — Informasi aktif yang dipilih oleh admin.</p>
    </div>

    {{-- Announcement List --}}
    @if($announcements->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-bullhorn" style="color: #a5b4fc;"></i>
        </div>
        <h3>Tidak Ada Pengumuman Aktif</h3>
        <p>Belum ada pengumuman yang ditampilkan untuk saat ini.<br>Silakan periksa kembali nanti.</p>
    </div>
    @else
    <div class="ann-list">
        @foreach($announcements as $idx => $ann)
        @php
            $typeClass = $ann->type;
            $icon = match($ann->type) {
                'urgent'  => '🚨',
                'warning' => '⚠️',
                default   => 'ℹ️',
            };
            $delay = $idx * 0.07;
        @endphp
        <div class="ann-card {{ $typeClass }}" style="animation-delay: {{ $delay }}s;">
            <div class="ann-icon {{ $typeClass }}">{{ $icon }}</div>
            <div class="ann-body">
                <div class="ann-meta">
                    <span class="ann-type-badge {{ $typeClass }}">
                        {{ $ann->type_label }}
                    </span>
                    <span class="ann-date">
                        <i class="fas fa-clock" style="font-size:8px;margin-right:3px;"></i>
                        {{ $ann->created_at->diffForHumans() }}
                    </span>
                </div>
                <p class="ann-title">{{ $ann->title }}</p>
                <p class="ann-content">{{ $ann->content }}</p>
                @if($ann->expires_at)
                <p class="ann-expires">
                    <i class="fas fa-hourglass-half" style="font-size:9px;"></i>
                    Berlaku hingga {{ $ann->expires_at->format('d M Y, H:i') }}
                </p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Footer --}}
    <div class="page-footer">
        <p>© {{ date('Y') }} {{ $schoolName }} · Halaman Informasi Publik</p>
    </div>

</div>
</body>
</html>
