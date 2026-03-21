@extends('layouts.app')

@section('title', 'Preview Surat - ' . ($configs['school_name'] ?? 'SesekaliCBT'))

@section('page-title', 'Preview Surat')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-fadeIn pb-20">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col gap-4">
        <nav class="flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
            <a href="{{ route('admin.letters.index') }}" class="hover:text-indigo-600 transition-colors">Generator Surat</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <a href="{{ route('admin.letters.form', $template) }}" class="hover:text-indigo-600 transition-colors">Pilih Penerima</a>
            <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
            <span class="text-indigo-600">Preview</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Preview Surat</h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">
                        Penerima: <span class="text-indigo-600">{{ $recipient->name }}</span>
                    </p>
                </div>
            </div>
            
            <div class="flex gap-3">
                
                <form action="{{ route('admin.letters.generate', $template) }}" method="POST" class="no-loading">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $recipient->id }}">
                    
                    {{-- Pass custom fields jika ada --}}
                    @if(isset($customFields) && !empty($customFields))
                        @foreach($customFields as $key => $value)
                            <input type="hidden" name="custom_fields[{{ $key }}]" value="{{ $value }}">
                        @endforeach
                    @endif

                    <button type="submit" class="px-8 py-4 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20 flex items-center gap-3">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                </form>
                <a href="{{ route('admin.letters.form', $template) }}" class="px-8 py-4 bg-white border border-gray-100 text-[11px] font-black uppercase tracking-widest rounded-2xl text-gray-500 hover:text-indigo-600 transition shadow-sm flex items-center gap-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Paper Preview -->
    <div class="bg-gray-200/50 p-4 md:p-12 rounded-[3.5rem] border border-gray-100 shadow-inner flex justify-center overflow-x-auto custom-scrollbar">
        <div class="bg-white w-[210mm] min-h-[297mm] shadow-2xl rounded-sm p-[2cm] md:p-[2.5cm] relative overflow-hidden flex flex-col">
            {{-- Kop Surat --}}
            @php
                $borderStyle = $configs['letterhead_border_style'] ?? 'double';
                $borderClass = match($borderStyle) {
                    'single' => 'border-b border-black',
                    'thick'  => 'border-b-4 border-black',
                    default  => 'border-b-[3px] border-double border-black',
                };
            @endphp

            <div class="flex items-center gap-4 pb-4 mb-6 {{ $borderClass }}">
                {{-- Logo --}}
                <div class="w-20 h-20 flex items-center justify-center 
                            overflow-hidden flex-shrink-0">
                    @if(isset($configs['logo']) && $configs['logo'])
                        <img src="{{ asset('storage/' . $configs['logo']) }}" 
                             class="w-full h-full object-contain">
                    @endif
                </div>

                {{-- Teks Kop --}}
                <div class="flex-1 text-center">
                    @if(!empty($configs['letterhead_foundation']))
                        <p class="text-[9pt] font-medium text-gray-700 uppercase 
                                  leading-tight mb-0.5">
                            {{ $configs['letterhead_foundation'] }}
                        </p>
                    @endif

                    <h1 class="text-[15pt] font-black text-black uppercase 
                               leading-tight tracking-tight">
                        {{ $configs['school_name'] ?? 'NAMA SEKOLAH' }}
                    </h1>

                    @if(!empty($configs['letterhead_program']))
                        <p class="text-[9pt] font-bold text-black uppercase 
                                  leading-tight mt-0.5">
                            PROGRAM KEAHLIAN : {{ $configs['letterhead_program'] }}
                        </p>
                    @endif

                    @if(!empty($configs['letterhead_email']) || !empty($configs['letterhead_website']))
                        <p class="text-[8pt] text-gray-600 mt-0.5">
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

                    <p class="text-[8.5pt] text-gray-700 mt-0.5">
                        {{ $configs['school_address'] ?? '' }}
                    </p>
                </div>
            </div>

            {{-- Letter Body --}}
            <div class="flex-1 text-black text-[12pt] leading-[1.8] space-y-4">
                {!! $bodyRendered !!}
            </div>

            {{-- Signature section --}}
            <div class="mt-20 self-end w-64 text-right flex flex-col items-center">
                <div class="w-full">
                    <p class="text-[12pt] font-medium">
                        {{ $configs['school_city'] ?? 'Kota' }}, 
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                    </p>
                    <p class="text-[12pt] font-medium mt-1 mb-5 italic">Kepala Sekolah,</p>
                    
                    @if($principal && $principal->signature && $principal->is_signature_active)
                        <div class="h-20 flex items-center justify-center mb-4">
                            <img src="{{ asset('storage/signatures/' . $principal->signature) }}" class="h-full object-contain mix-blend-multiply">
                        </div>
                    @else
                        <div class="h-20"></div>
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
                    @endphp
                    <p class="text-[12pt] font-black uppercase tracking-tight underline">{{ $principalName }}</p>
                    @if($principal && ($principal->nip || $principal->niy))
                        <p class="text-[11pt] font-bold mt-0.5">NIP. {{ $principal->nip ?? $principal->niy }}</p>
                    @endif
                </div>
            </div>
            
            {{-- Watermark Preview --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] rotate-[-45deg]">
                <span class="text-[10rem] font-black uppercase tracking-[0.5em]">PREVIEW</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Reset style untuk preview agar mirip PDF nantinya */
.paper-content p { margin-bottom: 1em; }
.paper-content { font-family: Arial, sans-serif; }

/* Custom scrollbar untuk preview horisontal di mobile */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
</style>
<script>
function printLetter() {
    const borderStyle = '{{ $configs['letterhead_border_style'] ?? 'double' }}';
    const borderCss = borderStyle === 'single' 
        ? 'border-bottom: 1px solid #000;' 
        : (borderStyle === 'thick' ? 'border-bottom: 4px solid #000;' : 'border-bottom: 3px double #000;');

    const win = window.open('', '_blank');
    win.document.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Print - {{ $recipient->name }}</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                @page { margin: 2.5cm; size: A4; }
                body { 
                    font-family: Arial, sans-serif;
                    font-size: 12pt;
                    line-height: 1.8;
                    color: #000;
                }
                .kop {
                    display: table;
                    width: 100%;
                    border-bottom: 3px solid #000;
                    padding-bottom: 12px;
                    margin-bottom: 24px;
                }
                .kop-logo {
                    display: table-cell;
                    width: 75px;
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
                    text-align: center;
                    padding: 0 10px;
                }
                .kop-text h1 {
                    font-size: 16pt;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 2px;
                }
                .kop-text p {
                    font-size: 10pt;
                    text-transform: uppercase;
                    font-weight: bold;
                }
                .body { margin-top: 16px; text-align: justify; }
                .body p { margin-bottom: 10px; }
                .signature {
                    margin-top: 40px;
                    width: 280px;
                    float: right;
                    text-align: center;
                }
                .signature img {
                    height: 65px;
                    object-fit: contain;
                    display: block;
                    margin: 8px auto;
                }
                .sig-name {
                    font-weight: bold;
                    text-decoration: underline;
                    text-transform: uppercase;
                    word-wrap: break-word;
                    white-space: normal;
                    line-height: 1.4;
                }
                .signature p {
                    word-wrap: break-word;
                    white-space: normal;
                }
                .clearfix { clear: both; }
            </style>
        </head>
        <body>
            <div class="kop" style="${borderCss}">
                <div class="kop-logo">
                    @if(isset($configs['logo']) && $configs['logo'] && file_exists(storage_path('app/public/' . $configs['logo'])))
                    <img src="{{ asset('storage/' . $configs['logo']) }}">
                    @endif
                </div>
                <div class="kop-text">
                    @if(!empty($configs['letterhead_foundation']))
                    <div style="font-size: 9pt; text-transform: uppercase;">{{ $configs['letterhead_foundation'] }}</div>
                    @endif
                    <h1>{{ $configs['school_name'] ?? 'NAMA SEKOLAH' }}</h1>
                    @if(!empty($configs['letterhead_program']))
                    <div style="font-size: 9pt; font-weight: bold; text-transform: uppercase;">PROGRAM KEAHLIAN : {{ $configs['letterhead_program'] }}</div>
                    @endif
                    <div style="font-size: 8pt; margin-top: 2px;">
                        @if(!empty($configs['letterhead_email'])) Pos-El : {{ $configs['letterhead_email'] }} @endif
                        @if(!empty($configs['letterhead_email']) && !empty($configs['letterhead_website'])) | @endif
                        @if(!empty($configs['letterhead_website'])) Laman : {{ $configs['letterhead_website'] }} @endif
                    </div>
                    <div style="font-size: 8.5pt; font-weight: normal; text-transform: none; margin-top: 2px;">{{ $configs['school_address'] ?? '' }}</div>
                </div>
            </div>

            <div class="body">{!! $bodyRendered !!}</div>

            <div class="signature">
                <p>{{ $configs['school_city'] ?? 'Kota' }}, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
                <p style="margin-bottom:20px;">Kepala Sekolah,</p>
                @if($principal && $principal->signature && $principal->is_signature_active)
                <img src="{{ asset('storage/signatures/' . $principal->signature) }}">
                @endif
                @php
                    $pName = '____________________';
                    if ($principal) {
                        $parts = [];
                        if ($principal->title_ahead) $parts[] = trim($principal->title_ahead);
                        $parts[] = trim($principal->name);
                        $pName = implode(' ', $parts);
                        if ($principal->title_behind) $pName .= ', ' . trim($principal->title_behind);
                    }
                @endphp
                <p class="sig-name">{{ $pName }}</p>
                @if($principal && ($principal->nip || $principal->niy))
                <p>NIP. {{ $principal->nip ?? $principal->niy }}</p>
                @endif
            </div>
            <div class="clearfix"></div>

            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() { window.close(); };
                };
            <\/script>
        </body>
        </html>
    `);
    win.document.close();
}
</script>
@endsection
