<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Kartu Pemilih — {{ $period->title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 6mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        table.page-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm 3mm;
            table-layout: fixed;
        }
        .card-cell {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .card-box {
            border: 1.5px dashed #64748b;
            border-radius: 6px;
            padding: 8px 10px;
            background: #ffffff;
            box-sizing: border-box;
            height: 52mm;
        }
        .header {
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .school-name {
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .period-title {
            font-size: 10px;
            font-weight: bold;
            color: #1e1b4b;
        }
        .student-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-meta {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .token-box {
            background: #0f172a;
            border-radius: 4px;
            padding: 6px 4px;
            text-align: center;
            margin-bottom: 5px;
        }
        .token-label {
            font-size: 7px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .token-code {
            font-size: 15px;
            font-weight: bold;
            color: #ffffff;
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
        }
        .footer-note {
            font-size: 7px;
            color: #64748b;
            text-align: center;
            line-height: 1.2;
            font-style: italic;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    @php
        // 10 kartu per halaman (2 kolom x 5 baris)
        $chunkedCards = $cards->chunk(10);
    @endphp

    @foreach($chunkedCards as $pageIndex => $pageCards)
        <table class="page-table">
            @php
                $rows = $pageCards->chunk(2);
            @endphp
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $card)
                        <td class="card-cell">
                            <div class="card-box">
                                <div class="header">
                                    <div class="school-name">{{ $schoolName }}</div>
                                    <div class="period-title">{{ $period->title }} &bull; KARTU PEMILIH</div>
                                </div>

                                <div class="student-name">{{ $card['name'] }}</div>
                                <div class="student-meta">
                                    NIS: <strong>{{ $card['nis'] }}</strong> &bull; Kelas: <strong>{{ $card['class_name'] }}</strong>
                                </div>

                                <div class="token-box">
                                    <div class="token-label">KODE TOKEN BILIK SUARA</div>
                                    <div class="token-code">{{ $card['token'] }}</div>
                                </div>

                                <div class="footer-note">
                                    Rahasia &bull; Sekali pakai &bull; Serahkan kertas ini ke panitia setelah memilih
                                </div>
                            </div>
                        </td>
                    @endforeach

                    @if($row->count() === 1)
                        <td class="card-cell"></td>
                    @endif
                </tr>
            @endforeach
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
