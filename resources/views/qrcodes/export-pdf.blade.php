<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Semua QR Code Aset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }

        .page {
            page-break-after: always;
        }

        .qr-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .qr-item {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .qr-code {
            margin-bottom: 5px;
        }

        .asset-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .code-content {
            font-size: 8pt;
            color: #666;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>QR Code Semua Aset</h1>
        <p>Sekolah Tinggi Teknologi Indonesia Cirebon</p>
        <p>Dicetak: {{ now()->format('d F Y H:i') }}</p>
    </div>

    <div class="qr-grid">
        @foreach($data as $item)
            <div class="qr-item">
                <div class="qr-code">
                    {!! $item['svg'] !!}
                </div>
                <div class="asset-name">
                    {{ $item['qrCode']->asset->name ?? 'N/A' }}
                </div>
                <div class="code-content">
                    {{ $item['qrCode']->code_content }}
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>