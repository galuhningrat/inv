<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Aset - SINADAS</title>

    <!-- Font Playfair Display untuk Logo SINADAS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap" rel="stylesheet">

    <style>
        @@page {
            size: A4;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sticker-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4mm;
            align-content: flex-start;
            justify-content: flex-start;
        }

        .sticker-card {
            width: 95mm;
            height: 36mm;
            background: #fff;
            border: 0.2mm solid #000;
            border-radius: 1mm;
            display: flex;
            overflow: hidden;
            page-break-inside: avoid;
            padding: 1.5mm;
        }

        /* KOLOM KIRI: QR CODE */
        .qr-box {
            width: 26mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-right: 1.5mm;
            border-right: 0.2mm dashed #000;
        }

        .qr-frame {
            width: 23mm;
            height: 23mm;
            background: #fff;
            padding: 0.5mm;
            border: 0.2mm solid #000;
        }

        .qr-frame svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .scan-text {
            margin-top: 1mm;
            font-size: 4.5pt;
            font-weight: bold;
            text-align: center;
            color: #64748b;
            line-height: 1.1;
        }

        /* KOLOM KANAN: INFORMASI */
        .info-box {
            flex: 1;
            padding-left: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-top: 1mm;
            padding-bottom: 0.5mm;
            position: relative;
            z-index: 1;
        }

        /* WATERMARK LOGO STTI */
        .info-box::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: 90%;
            background-image: url('{{ asset("assets/logo-stti.png") }}');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.12;
            z-index: -1;
            pointer-events: none;
        }

        .info-box>* {
            position: relative;
            z-index: 2;
        }

        /* KOP */
        .header-center {
            text-align: center;
            margin-bottom: 0;
        }

        .header-diterbitkan {
            font-family: 'Times New Roman', serif;
            font-size: 5.5pt;
            font-weight: bold;
            color: #000;
            display: block;
            margin-bottom: 0.5mm;
            line-height: 1;
        }

        .header-sinadas {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 900;
            font-size: 15pt;
            color: #000;
            display: block;
            line-height: 1;
            letter-spacing: -0.5px;
            margin-bottom: 0.5mm;
        }

        .header-subtitle {
            font-family: 'Times New Roman', serif;
            font-size: 5pt;
            font-weight: bold;
            color: #000;
            display: block;
            line-height: 1;
        }

        .header-divider {
            border-bottom: 0.2mm solid #000;
            width: 100%;
            margin-top: 1mm;
            margin-bottom: 1mm;
        }

        /* Nama Aset (Sejajar) */
        .data-group {
            display: flex;
            align-items: baseline;
            gap: 1mm;
            margin-bottom: 1mm;
        }

        .field-label {
            font-size: 5.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            line-height: 1;
            white-space: nowrap;
        }

        .asset-name {
            font-size: 6.5pt;
            font-weight: bold;
            color: #000;
            line-height: 1.1;
            word-wrap: break-word;
        }

        /* 🔥 KOTAK ID ASET (BACKGROUND DIHAPUS AGAR TRANSPARAN) 🔥 */
        .asset-id-box {
            background: transparent;
            /* Dibuat transparan agar logo tembus */
            border: 0.2mm solid #000;
            padding: 0.8mm 1.5mm;
            border-radius: 0.5mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-bottom: 1mm;
        }

        .id-label {
            font-size: 5.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1;
        }

        .id-value {
            font-family: 'Times New Roman', Times, serif;
            font-size: 6pt;
            font-weight: bold;
            color: #000;
            word-break: break-all;
            line-height: 1.1;
            margin-top: 0.2mm;
        }

        /* JENIS ASET */
        .asset-type-block {
            margin-bottom: 0;
        }

        .asset-type {
            font-size: 6.5pt;
            font-weight: bold;
            color: #000;
        }

        .specs-list {
            list-style: none;
            padding: 0;
            margin: 0.5mm 0 0 0;
            font-size: 5.5pt;
            color: #333;
            line-height: 1.1;
        }

        .specs-list li {
            display: flex;
            gap: 1mm;
        }

        .specs-list li::before {
            content: "*";
            color: #000;
            font-weight: bold;
        }

        @@media screen {
            body {
                background: #cbd5e1;
                padding: 20mm;
                display: flex;
                justify-content: center;
            }

            .sticker-container {
                background: #fff;
                padding: 10mm;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                width: 210mm;
                min-height: 297mm;
            }
        }

        @@media print {
            body {
                background: #fff;
                padding: 0;
            }

            .sticker-container {
                background: transparent;
                padding: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }

            .qr-box {
                background: #fff;
            }
        }
    </style>
</head>

<body>
    @php
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

    <!-- Wadah Utama -->
    <div class="sticker-container">

        <div class="sticker-card">

            <!-- KIRI: QR Code -->
            <div class="qr-box">
                <div class="qr-frame">
                    {!! $qrCodeSvg !!}
                </div>
                <div class="scan-text">
                    PINDAI UNTUK<br>LIHAT DETAIL
                </div>
            </div>

            <!-- KANAN: Info -->
            <div class="info-box">
                <!-- KOP -->
                <div class="header-center">
                    <span class="header-diterbitkan">Diterbitkan melalui sistem:</span>
                    <span class="header-sinadas">SINADAS</span>
                    <span class="header-subtitle">Sistem Informasi Inventaris dan Administrasi Aset</span>
                </div>

                <!-- Garis Pemisah Kop -->
                <div class="header-divider"></div>

                <!-- Nama Aset -->
                <div class="data-group">
                    <span class="field-label">Nama Model / Aset:</span>
                    <span class="asset-name">{{ $qrCode->asset->name ?? '-' }}</span>
                </div>

                <!-- ID Aset -->
                <div class="asset-id-box">
                    <span class="id-label">ID ASET</span>
                    <span class="id-value">{{ $qrCode->asset->asset_id ?? '-' }}</span>
                </div>

                <!-- JENIS ASET -->
                <div class="asset-type-block">
                    <div style="display: flex; align-items: baseline; gap: 1mm; margin-bottom: 0;">
                        <span class="field-label" style="margin-bottom: 0;">Jenis Aset & Spesifikasi:</span>
                        <span class="asset-type">{{ $qrCode->asset->batch->assetType->name ?? '-' }}</span>
                    </div>

                    @if(count($specs) > 0)
                    <ul class="specs-list">
                        @foreach($specs as $label => $value)
                        <li>
                            <span>{{ $label }}: <strong>{{ $value }}</strong></span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            if (document.fonts) {
                document.fonts.ready.then(function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                });
            } else {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>

</html>