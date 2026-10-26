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
            font-size: 6.5pt;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.1;
            margin-bottom: 0.8mm;
        }
        .card-name {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
            height: 6.8mm;
            overflow: hidden;
            word-wrap: break-word;
            margin-bottom: 0.8mm;
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
            margin-bottom: 1mm;
        }
        .token-box {
            border: 0.5pt solid #94a3b8;
            border-radius: 2px;
            background: #f8fafc;
            padding: 1.2mm 0.5mm;
            text-align: center;
            margin-bottom: 1mm;
        }
        .token-label {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8pt;
            line-height: 1;
            margin-bottom: 0.5mm;
        }
        .token-code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 1.2pt;
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
                    @endphp
                    @if($card)
                        <td class="card-cell">
                            <div class="card-inner">
                                <div class="card-header">{{ $schoolName }} - {{ $period->title }}</div>
                                <div class="card-name {{ mb_strlen($card['name'] ?? '') > 24 ? 'name-sm' : '' }}">
                                    {{ $card['name'] }}
                                </div>
                                <div class="card-meta">
                                    NIS {{ $card['nis'] }} - Kelas {{ $card['class_name'] }}
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
