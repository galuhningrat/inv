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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
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
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Sekolah Tinggi Teknologi Indonesia Cirebon</p>
        <p>Dicetak: {{ now()->format('d F Y H:i') }} | Oleh: {{ $user->name }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Aset</h3>
            <p style="font-size: 18pt; font-weight: bold; margin: 10px 0;">{{ $data['total_assets'] }}</p>
            <p style="color: #666;">unit terdaftar</p>
        </div>
        <div class="stat-card">
            <h3>Total Nilai Aset</h3>
            <p style="font-size: 14pt; font-weight: bold; margin: 10px 0;">Rp
                {{ number_format($data['total_value'], 0, ',', '.') }}</p>
            <p style="color: #666;">nilai inventaris</p>
        </div>
        <div class="stat-card">
            <h3>Biaya Pemeliharaan</h3>
            <p style="font-size: 14pt; font-weight: bold; margin: 10px 0;">Rp
                {{ number_format($data['maintenance_cost'], 0, ',', '.') }}</p>
            <p style="color: #666;">total biaya</p>
        </div>
        <div class="stat-card">
            <h3>Rata-rata Nilai</h3>
            <p style="font-size: 14pt; font-weight: bold; margin: 10px 0;">Rp
                {{ number_format($data['average_value'], 0, ',', '.') }}</p>
            <p style="color: #666;">per aset</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Aset Terdaftar</td>
                <td><strong>{{ $data['total_assets'] }} unit</strong></td>
            </tr>
            <tr>
                <td>Total Nilai Aset</td>
                <td><strong>Rp {{ number_format($data['total_value'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Total Biaya Pemeliharaan</td>
                <td><strong>Rp {{ number_format($data['maintenance_cost'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Rata-rata Nilai Aset</td>
                <td><strong>Rp {{ number_format($data['average_value'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Jumlah Record Pemeliharaan</td>
                <td><strong>{{ $data['maintenance_records'] }} record</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Inventaris STTI Cirebon</p>
    </div>
</body>

</html>