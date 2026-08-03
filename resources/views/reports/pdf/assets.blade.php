<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 14pt;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 8pt;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 8pt;
            color: #666;
        }

        .total {
            font-weight: bold;
            background: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Sekolah Tinggi Teknologi Indonesia Cirebon</p>
        <p>Dicetak: {{ now()->format('d F Y H:i') }} | Oleh: {{ $user->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Aset</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Merek</th>
                <th>Lokasi</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $asset)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $asset->asset_id }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->assetType->name }}</td>
                    <td>{{ $asset->brand }}</td>
                    <td>{{ $asset->location }}</td>
                    <td>{{ $asset->condition }}</td>
                    <td>{{ $asset->status }}</td>
                    <td>Rp {{ number_format($asset->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="8" style="text-align: right;">Total Nilai Aset:</td>
                <td>Rp {{ number_format($data->sum('price'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Inventaris STTI Cirebon</p>
    </div>
</body>

</html>