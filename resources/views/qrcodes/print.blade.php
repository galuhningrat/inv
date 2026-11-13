<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code - {{ $qrCode->asset->name }}</title>
    <style>
        @page {
            size: 70mm 45mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 2mm;
            font-family: Arial, sans-serif;
            font-size: 8pt;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 70mm;
            height: 45mm;
        }

        .label-container {
            text-align: center;
        }

        .institution-name {
            font-weight: bold;
            font-size: 7pt;
            margin: 0;
        }

        .asset-name {
            font-weight: bold;
            font-size: 9pt;
            margin: 1mm 0;
        }

        .qr-code {
            margin-top: 2mm;
        }

        .qr-code svg {
            width: 30mm;
            height: 30mm;
        }

        .code-number {
            font-size: 7pt;
            letter-spacing: 1px;
            margin-top: 1mm;
        }
    </style>
</head>

<body>
    <div class="label-container">
        <div class="institution-name">Inventaris STT Indonesia Cirebon</div>
        <div class="asset-name">{{ $qrCode->asset->name }}</div>
        <div class="qr-code">{!! $qrCodeSvg !!}</div>
        <div class="code-number">{{ $qrCode->code_content }}</div>
    </div>
    <script>
        window.onload = function () {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>