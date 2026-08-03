<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset - Inventaris STTI Cirebon</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --background: #f8fafc;
            --card-bg: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .header img {
            width: 60px;
            height: 60px;
            margin-bottom: 0.5rem;
        }

        .header h1 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: 0 0 16px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .asset-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--border);
        }

        .asset-info {
            padding: 1.5rem;
        }

        .asset-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .asset-id {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .info-item {
            padding: 0.75rem;
            background: var(--background);
            border-radius: 8px;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .status-borrowed {
            background: #fef3c7;
            color: #92400e;
        }

        .status-maintenance {
            background: #fee2e2;
            color: #991b1b;
        }

        .condition-good {
            background: #d1fae5;
            color: #065f46;
        }

        .condition-minor {
            background: #fef3c7;
            color: #92400e;
        }

        .condition-major {
            background: #fee2e2;
            color: #991b1b;
        }

        .error-container {
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: var(--text-secondary);
        }

        .footer {
            text-align: center;
            padding: 1rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/logo-stti.png') }}" alt="Logo STTI">
            <h1>Sistem Inventaris</h1>
            <p>STT Indonesia Cirebon</p>
        </div>

        <div class="card">
            @if($error)
                <div class="error-container">
                    <div class="error-icon">❌</div>
                    <h2 class="error-title">Aset Tidak Ditemukan</h2>
                    <p class="error-message">{{ $error }}</p>
                </div>
            @else
                <img src="{{ $asset->image ? Storage::url($asset->image) : asset('assets/default-asset.png') }}"
                    alt="{{ $asset->name }}" class="asset-image">

                <div class="asset-info">
                    <h2 class="asset-name">{{ $asset->name }}</h2>
                    <p class="asset-id">ID: {{ $asset->asset_id }}</p>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Jenis Aset</div>
                            <div class="info-value">{{ $asset->assetType->name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Merek</div>
                            <div class="info-value">{{ $asset->brand }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Lokasi</div>
                            <div class="info-value">{{ $asset->location }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Nomor Seri</div>
                            <div class="info-value" style="font-size: 0.75rem;">{{ $asset->serial_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span
                                    class="status-badge {{ $asset->status === 'Tersedia' ? 'status-available' : ($asset->status === 'Dipinjam' ? 'status-borrowed' : 'status-maintenance') }}">
                                    {{ $asset->status }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kondisi</div>
                            <div class="info-value">
                                <span
                                    class="status-badge {{ $asset->condition === 'Baik' ? 'condition-good' : ($asset->condition === 'Rusak Ringan' ? 'condition-minor' : 'condition-major') }}">
                                    {{ $asset->condition }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item" style="grid-column: span 2;">
                            <div class="info-label">Tanggal Pembelian</div>
                            <div class="info-value">{{ $asset->purchase_date->format('d F Y') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} STT Indonesia Cirebon</p>
            <p>Sistem Inventaris Kampus v1.0</p>
        </div>
    </div>
</body>

</html>