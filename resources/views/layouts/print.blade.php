<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Raport')</title>
    @vite(['resources/css/app.css'])
    <style>
  * { box-sizing: border-box !important; }

  html, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff;
      font-family: 'Times New Roman', Times, serif;
      color: #000;
  }

  @media screen {
      body { padding: 24px; background: #e5e7eb; }
      .report-page {
          width: 210mm;
          min-height: 297mm;
          padding: 1.5cm 1.5cm 1.5cm 2cm;
          margin: 0 auto 24px auto;
          background: white;
          box-shadow: 0 4px 20px rgba(0,0,0,0.15);
          position: relative;
          overflow: hidden;
      }
  }

  @media print {
      html, body {
          width: 210mm !important;
          background: white !important;
          overflow: visible !important;
          margin: 0 !important;
          padding: 0 !important;
      }

      /*
       * KUNCI: margin dihandle via padding di .report-page
       * bukan via @page margin — lebih reliable di semua browser
       * @page margin: 0 agar tidak ada margin ganda
       */
      @page {
          size: A4 portrait;
          margin: 0;
      }

      .report-page {
          width: 210mm !important;
          /*
           * Tinggi FIXED bukan min-height agar browser tahu
           * persis batas setiap halaman — ini kunci agar
           * konten tidak bocor ke halaman berikutnya
           */
          height: 297mm !important;
          min-height: unset !important;
          /*
           * Padding sebagai pengganti margin kertas
           * total vertikal: 1.5cm + 1.5cm = 3cm
           * total horizontal: 2cm + 1.5cm = 3.5cm
           */
          padding: 1.5cm 1.5cm 1.5cm 2cm !important;
          margin: 0 !important;
          box-shadow: none !important;
          page-break-after: always !important;
          break-after: page !important;
          page-break-inside: avoid !important;
          break-inside: avoid !important;
          background: white !important;
          position: relative !important;
          overflow: hidden !important;
          display: block !important;
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
      }

      .report-page:last-child {
          page-break-after: avoid !important;
          break-after: avoid !important;
      }

      /* Sembunyikan elemen non-print */
      #batch-bar,
      #loading-screen,
      .no-print {
          display: none !important;
      }

      /* Watermark tetap muncul via ::before pseudo-element */
      .report-watermark {
          display: none !important;
      }
  }
    </style>
</head>
<body>
    @yield('content')

    @yield('scripts')
</body>
</html>
