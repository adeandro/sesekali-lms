<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Pemilih — {{ $period->title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.7cm;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }
        table.page-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        .card-row {
            height: 3.5cm;
        }
        .card-cell {
            width: 33.333%;
            height: 3.5cm;
            vertical-align: top;
            padding: 0;
            border: 0.5pt dashed #94a3b8;
        }
        .empty-cell {
            border: 0.5pt dashed transparent;
        }
        .card-inner {
            padding: 0.15cm;
            height: 3.2cm;
            overflow: hidden;
        }
        .card-header {
            font-size: 6pt;
            color: #64748b;
            line-height: 1.2;
            margin-bottom: 0.8mm;
            overflow: hidden;
            max-height: 9mm; /* cukup 2 baris × ~4.5mm */
        }
        .card-header-school {
            font-size: 6pt;
            font-weight: bold;
            color: #475569;
            line-height: 1.2;
            overflow: hidden;
        }
        .card-header-period {
            font-size: 6pt;
            color: #64748b;
            line-height: 1.2;
            overflow: hidden;
        }
        .card-name {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
            height: 6.8mm;
            overflow: hidden;
            word-wrap: break-word;
            margin-bottom: 0.5mm;
        }
        .card-name.name-sm {
            font-size: 8pt;
            line-height: 1.15;
        }
        .card-meta {
            font-size: 7.5pt;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.1;
            margin-bottom: 0.8mm;
        }
        .token-box {
            border: 0.5pt solid #94a3b8;
            border-radius: 2px;
            background: #f8fafc;
            padding: 0.8mm 0.5mm;
            text-align: center;
            margin-bottom: 0.8mm;
        }
        .token-label {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8pt;
            line-height: 1;
            margin-bottom: 0.4mm;
        }
        .token-code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 1pt;
            line-height: 1;
        }
        .card-footer {
            font-size: 5.5pt;
            color: #64748b;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            line-height: 1;
        }
    </style>
</head>
<body>

@php
    $cols = 3;
    $rows = 8;
    $perPage = $cols * $rows;

    // Tanggal pemilihan (hanya jika event_date ada)
    $eventDateStr = '';
    if (!empty($period->event_date)) {
        try {
            \Carbon\Carbon::setLocale('id');
            $eventDateStr = \Carbon\Carbon::parse($period->event_date)->translatedFormat('j F Y');
        } catch (\Throwable $e) {
            $eventDateStr = '';
        }
    }

    // Header baris 2: "Judul Periode" + " — tanggal" jika ada
    $periodLine = $period->title;
    if ($eventDateStr) {
        $periodLine .= ' — ' . $eventDateStr;
    }

    // Urutkan kartu A-Z case-insensitive
    $sortedCards = $cards->sortBy(fn($c) => mb_strtolower($c['name'] ?? ''))->values();
    $pages = $sortedCards->chunk($perPage);
@endphp

@foreach($pages as $pageIndex => $pageCards)
    <table class="page-table" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
        @for($r = 0; $r < $rows; $r++)
            <tr class="card-row">
                @for($c = 0; $c < $cols; $c++)
                    @php
                        $cardIndex = $r * $cols + $c;
                        $card = $pageCards->get($cardIndex);
                        $cardName = mb_strtoupper($card['name'] ?? '', 'UTF-8');
                    @endphp
                    @if($card)
                        <td class="card-cell">
                            <div class="card-inner">
                                {{-- Header 2 baris: nama sekolah + judul periode + tanggal --}}
                                <div class="card-header">
                                    <div class="card-header-school">{{ $schoolName }}</div>
                                    <div class="card-header-period">{{ $periodLine }}</div>
                                </div>
                                <div class="card-name {{ mb_strlen($card['name'] ?? '') > 24 ? 'name-sm' : '' }}">
                                    {{ $cardName }}
                                </div>
                                <div class="card-meta">
                                    @if($card['class_name'] === 'GURU & STAF')
                                        ID {{ $card['nis'] }} &bull; GURU &amp; STAF
                                    @else
                                        NIS {{ $card['nis'] }} &bull; Kelas {{ $card['class_name'] }}
                                    @endif
                                </div>
                                <div class="token-box">
                                    <div class="token-label">TOKEN</div>
                                    <div class="token-code">{{ $card['token'] }}</div>
                                </div>
                                <div class="card-footer">
                                    Rahasia - sekali pakai - serahkan ke panitia
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="card-cell empty-cell"></td>
                    @endif
                @endfor
            </tr>
        @endfor
    </table>
@endforeach

</body>
</html>
