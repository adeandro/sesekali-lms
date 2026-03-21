<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat - {{ $recipient->name }}</title>
    <style>
        @page { 
            margin: 2.5cm 2.5cm 2cm 2.5cm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12pt; 
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .kop-surat {
            display: table;
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .kop-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .kop-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .kop-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
            text-align: center;
        }
        .kop-text h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-text p {
            font-size: 10pt;
            margin: 0;
            line-height: 1.4;
            text-transform: uppercase;
            font-weight: bold;
        }
        .letter-body {
            margin-top: 20px;
            text-align: justify;
        }
        .letter-body p {
            margin-bottom: 15px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: left;
            width: 6.5cm;
            float: right;
        }
        .signature-section p {
            margin: 0;
            line-height: 1.4;
        }
        .signature-img {
            height: 70px;
            margin: 10px 0;
            display: block;
        }
        .principal-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .clear {
            clear: both;
        }
        /* Styling for placeholders inserted by Quill */
        .placeholder-tag {
            font-family: Arial, sans-serif !important;
            background: transparent !important;
            color: black !important;
            padding: 0 !important;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Dinamis --}}
    @php
        $borderStyle = $configs['letterhead_border_style'] ?? 'double';
        $borderCss = match($borderStyle) {
            'single' => 'border-bottom: 1px solid #000;',
            'thick'  => 'border-bottom: 4px solid #000;',
            default  => 'border-bottom: 3px double #000;',
        };
    @endphp

    <div style="display: table; width: 100%; padding-bottom: 10px; 
                margin-bottom: 20px; {{ $borderCss }}">
        
        {{-- Logo --}}
        <div style="display: table-cell; width: 75px; vertical-align: middle;">
            @if(isset($configs['logo']) && $configs['logo'])
                <img src="{{ storage_path('app/public/' . $configs['logo']) }}" 
                    style="width: 70px; height: 70px; object-fit: contain;">
            @endif
        </div>

        {{-- Teks Kop --}}
        <div style="display: table-cell; vertical-align: middle; 
                    text-align: center; padding: 0 10px;">
            
            @if(!empty($configs['letterhead_foundation']))
                <p style="margin: 0; font-size: 9pt; font-weight: normal; 
                        text-transform: uppercase; line-height: 1.3;">
                    {{ $configs['letterhead_foundation'] }}
                </p>
            @endif

            <h1 style="margin: 2px 0; font-size: 15pt; font-weight: bold; 
                    text-transform: uppercase; line-height: 1.2;">
                {{ $configs['school_name'] ?? 'NAMA SEKOLAH' }}
            </h1>

            @if(!empty($configs['letterhead_program']))
                <p style="margin: 1px 0; font-size: 9pt; font-weight: bold; 
                        text-transform: uppercase; line-height: 1.3;">
                    PROGRAM KEAHLIAN : {{ $configs['letterhead_program'] }}
                </p>
            @endif

            @if(!empty($configs['letterhead_email']) || !empty($configs['letterhead_website']))
                <p style="margin: 1px 0; font-size: 8pt; line-height: 1.3; color: #333;">
                    @if(!empty($configs['letterhead_email']))
                        Pos-El : {{ $configs['letterhead_email'] }}
                    @endif
                    @if(!empty($configs['letterhead_email']) && !empty($configs['letterhead_website']))
                        &nbsp;|&nbsp;
                    @endif
                    @if(!empty($configs['letterhead_website']))
                        Laman : {{ $configs['letterhead_website'] }}
                    @endif
                </p>
            @endif

            <p style="margin: 1px 0; font-size: 8.5pt; line-height: 1.3;">
                {{ $configs['school_address'] ?? '' }}
            </p>
        </div>
    </div>

    {{-- Body Surat --}}
    <div class="letter-body">
        {!! $bodyRendered !!}
    </div>

    {{-- Tanda Tangan --}}
    <div class="signature-section">
        <p>{{ $configs['school_city'] ?? 'Kota' }}, 
           {{ \Carbon\Carbon::parse($letter->issued_date ?? now())->locale('id')->translatedFormat('d F Y') }}</p>
        <p style="margin-bottom: 5px;">Kepala Sekolah,</p>
        
        @if($principal && $principal->signature && $principal->is_signature_active)
            <img class="signature-img" src="{{ storage_path('app/public/signatures/' . $principal->signature) }}">
        @else
            <div style="height: 75px;"></div>
        @endif

        @php
            $principalName = '____________________';
            if ($principal) {
                $parts = [];
                if ($principal->title_ahead) $parts[] = trim($principal->title_ahead);
                $parts[] = trim($principal->name);
                $principalName = implode(' ', $parts);
                if ($principal->title_behind) $principalName .= ', ' . trim($principal->title_behind);
            }
            $principalNip = $principal?->nip ?? $principal?->niy ?? null;
        @endphp
        <p class="principal-name">{{ $principalName }}</p>
        @if($principalNip)
            <p>NIP. {{ $principalNip }}</p>
        @endif
    </div>
    <div class="clear"></div>

</body>
</html>
