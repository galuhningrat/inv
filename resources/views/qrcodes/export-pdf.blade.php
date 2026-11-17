<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Label QR Code - SINADAS</title>

    <!-- Font Playfair Display untuk Logo SINADAS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap" rel="stylesheet">

    <style>
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8pt;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Tabel Utama pembungkus Grid (2 kolom) */
        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid>tbody>tr>td {
            width: 50%;
            vertical-align: top;
            padding: 2mm;
        }

        /* Kotak Stiker */
        .sticker {
            width: 100%;
            height: 36mm;
            border: 0.3mm solid #000;
            padding: 2mm;
            box-sizing: border-box;
            page-break-inside: avoid;
            position: relative;
            border-radius: 1mm;
        }

        /* Layout dalam stiker */
        table.inner {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        /* Kolom QR */
        td.qr-col {
            width: 28mm;
            vertical-align: middle;
            text-align: center;
            border-right: 0.3mm dashed #000;
            padding-right: 2mm;
        }

        .qr-img {
            width: 24mm;
            height: 24mm;
            display: block;
            margin: 0 auto;
            border: 0.2mm solid #000;
            /* Tambahkan border seperti di print.blade */
            padding: 0.5mm;
        }

        .scan-text {
            font-size: 4.5pt;
            font-weight: bold;
            color: #64748b;
            text-align: center;
            margin-top: 1mm;
            line-height: 1.1;
        }

        /* Kolom Info */
        td.info-col {
            vertical-align: top;
            padding-left: 3mm;
            position: relative;
            /* Penting untuk watermark */
            overflow: hidden;
        }

        /* WATERMARK LOGO STTI */
        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            opacity: 0.12;
            z-index: 0;
            /* Di belakang teks */
        }

        /* Konten teks di atas watermark */
        .info-content {
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 0.2mm solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1.5mm;
        }

        .header-1 {
            font-size: 5.5pt;
            font-weight: bold;
        }

        .header-2 {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 900;
            font-size: 15pt;
            line-height: 1;
            margin: 0.5mm 0;
            letter-spacing: -0.5px;
        }

        .header-3 {
            font-size: 5pt;
            font-weight: bold;
        }

        /* Data Rows */
        .data-row {
            margin-bottom: 1mm;
            font-size: 6pt;
        }

        .label {
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            font-size: 5.5pt;
        }

        .value {
            font-weight: bold;
            color: #000;
            font-size: 7pt;
        }

        /* ID Box */
        .id-box {
            border: 0.2mm solid #000;
            padding: 1mm;
            margin-bottom: 1mm;
        }

        .id-label {
            font-size: 5.5pt;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
        }

        .id-value {
            font-size: 6.5pt;
            font-weight: bold;
            word-break: break-all;
            line-height: 1.1;
            margin-top: 0.5mm;
        }

        /* Specs */
        .specs {
            font-size: 5.5pt;
            color: #333;
            margin-top: 0.5mm;
            line-height: 1.2;
        }
    </style>
</head>

<body>
    @php
    // Pecah data menjadi 2 per baris
    $chunks = array_chunk($qrCodesData, 2);
    @endphp

    <table class="grid">
        @foreach($chunks as $chunk)
        <tr>
            @foreach($chunk as $data)
            @php
            $qrCode = $data['qrCode'];
            $qrCodeBase64 = $data['qrCodeBase64'];

            $assetTypeCode = $qrCode->asset->batch->assetType->code ?? '';
            $schema = \App\Models\AssetType::getSchema($assetTypeCode);
            $batchData = $qrCode->asset->batch->category_data ?? [];
            $unitData = $qrCode->asset->unit_data ?? [];

            $specs = [];
            foreach ($schema as $key => $field) {
            if ($key === 'subkategori') {
            $val = $batchData[$key] ?? $unitData[$key] ?? null;
            if (!empty($val)) {
            $specs[$field['label'] ?? $key] = ucfirst($val);
            }
            }
            }
            @endphp
            <td>
                <div class="sticker">
                    <table class="inner">
                        <tr>
                            <td class="qr-col">
                                <img src="{{ $qrCodeBase64 }}" class="qr-img" alt="QR">
                                <div class="scan-text">PINDAI UNTUK<br>LIHAT DETAIL</div>
                            </td>
                            <td class="info-col">
                                {{-- 🔥 WATERMARK LOGO DI SINI 🔥 --}}
                                {{-- Gunakan public_path() agar DomPDF bisa membaca gambar lokal. Jika gagal, ganti dengan asset() --}}
                                <img src="{{ public_path('assets/logo-stti.png') }}" class="watermark-logo" alt="Watermark">

                                {{-- Bungkus konten dengan class info-content agar berada di atas watermark --}}
                                <div class="info-content">
                                    <div class="header">
                                        <div class="header-1">Diterbitkan melalui sistem:</div>
                                        <div class="header-2">SINADAS</div>
                                        <div class="header-3">Sistem Informasi Inventaris dan Administrasi Aset</div>
                                    </div>
                                    <div class="data-row">
                                        <span class="label">Nama Model / Aset:</span>
                                        <span class="value">{{ $qrCode->asset->name ?? '-' }}</span>
                                    </div>
                                    <div class="id-box">
                                        <div class="id-label">ID ASET</div>
                                        <div class="id-value">{{ $qrCode->asset->asset_id ?? '-' }}</div>
                                    </div>
                                    <div class="data-row">
                                        <span class="label">Jenis Aset & Spesifikasi:</span>
                                        <span class="value">{{ $qrCode->asset->batch->assetType->name ?? '-' }}</span>
                                    </div>
                                    @if(count($specs) > 0)
                                    <div class="specs">
                                        @foreach($specs as $label => $value)
                                        * {{ $label }}: <strong>{{ $value }}</strong><br>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            @endforeach

            {{-- Jika baris hanya berisi 1 item, tambahkan sel kosong --}}
            @if(count($chunk) == 1)
            <td></td>
            @endif
        </tr>
        @endforeach
    </table>
</body>

</html>