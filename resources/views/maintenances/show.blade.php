@extends('layouts.app')

@section('title', 'Detail Tiket Pemeliharaan')
@section('page-title', 'Detail Tiket Pemeliharaan')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Tiket {{ $maintenance->maintenance_id }}</h3>
        <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div style="padding: 2rem;">
        {{-- Status Banner --}}
        <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; border-radius: 8px;
            background: {{ $maintenance->status === 'Menunggu Verifikasi' ? '#fef3c7' : ($maintenance->status === 'Selesai' ? '#d1fae5' : '#dbeafe') }};
            border: 1px solid {{ $maintenance->status === 'Menunggu Verifikasi' ? '#f59e0b' : ($maintenance->status === 'Selesai' ? '#10b981' : '#3b82f6') }};">
            <p style="margin: 0; font-weight: 600;">
                Status:
                <span class="status-badge {{ $maintenance->status_badge_class }}">{{ $maintenance->status }}</span>
            </p>
            @if($maintenance->isAwaitingVerification())
            <p style="margin: 0.5rem 0 0; font-size: 0.85rem; color: #78350f;">
                ⏳ Tiket ini menunggu verifikasi Kalab/PJ sebelum dapat diproses Tim Pemeliharaan.
            </p>
            @endif
        </div>

        {{-- Info Aset --}}
        <div class="detail-row" style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="detail-image" style="flex: 0 0 200px;">
                <img src="{{ $maintenance->asset->image_url }}" alt="{{ $maintenance->asset->batch->name ?? 'Aset' }}"
                    style="width: 100%; border-radius: 8px; object-fit: cover;">
            </div>
            <div class="detail-info" style="flex: 1;">
                <p><strong>Aset:</strong> {{ $maintenance->asset->batch->name ?? '-' }} ({{ $maintenance->asset->asset_id }})</p>
                <p><strong>Jenis Pemeliharaan:</strong> {{ $maintenance->type }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $maintenance->maintenance_date->format('d F Y') }}</p>
                <p><strong>Dilaporkan oleh:</strong> {{ $maintenance->recorder->name ?? '-' }}</p>
                <p><strong>Keterangan Awal:</strong> {{ $maintenance->description }}</p>
            </div>
        </div>

        <hr style="margin: 1.5rem 0;">

        {{-- ===== BLOK VERIFIKASI (muncul hanya jika belum diverifikasi) ===== --}}
        @if($maintenance->isAwaitingVerification())
        @can('verify', $maintenance)
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h4 style="margin: 0 0 1rem; color: #92400e;">🔍 Verifikasi Laporan</h4>
            <p style="margin: 0 0 1rem; font-size: 0.9rem; color: #78350f;">
                Anda ({!! Auth::user()->level !!}) berwenang memverifikasi laporan ini.
                Setelah diverifikasi, tiket akan diteruskan ke Tim Pemeliharaan.
            </p>

            <form action="{{ route('maintenances.verify', $maintenance) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="verification_notes">Catatan Verifikasi <small style="font-weight: normal;">(opsional)</small></label>
                    <textarea id="verification_notes" name="verification_notes" class="form-control" rows="2"
                        placeholder="Misal: Kerusakan sesuai laporan, silakan proses.">{{ old('verification_notes') }}</textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Verifikasi laporan ini dan teruskan ke Tim Pemeliharaan?')">
                        ✅ Verifikasi & Teruskan
                    </button>
                </div>
            </form>
        </div>
        @else
        <div style="background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; color: #6b7280;">
                ⏳ Menunggu verifikasi dari Kalab/PJ Sarpras. Anda tidak memiliki wewenang untuk memverifikasi tiket ini.
            </p>
        </div>
        @endcan
        @endif

        {{-- ===== BLOK INFO VERIFIKASI (jika sudah diverifikasi) ===== --}}
        @if($maintenance->verified_at)
        <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
            <h4 style="margin: 0 0 0.5rem; color: #065f46;">✅ Sudah Diverifikasi</h4>
            <p style="margin: 0; font-size: 0.9rem; color: #065f46;">
                <strong>Oleh:</strong> {{ $maintenance->verifier->name ?? '-' }} ({{ $maintenance->verifier->level ?? '-' }})<br>
                <strong>Tanggal:</strong> {{ $maintenance->verified_at->format('d F Y H:i') }}<br>
                @if($maintenance->verification_notes)
                <strong>Catatan:</strong> {{ $maintenance->verification_notes }}
                @endif
            </p>
        </div>
        @endif

        {{-- ===== FORM UPDATE STATUS (hanya jika sudah diverifikasi) ===== --}}
        @if(!$maintenance->isAwaitingVerification())
        @can('update', $maintenance)
        <h4 style="margin-bottom: 1rem;">Update Progres Pemeliharaan</h4>

        <form action="{{ route('maintenances.update', $maintenance) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status <span style="color: red;">*</span></label>
                    <select id="status" name="status" class="form-control @error('status') error @enderror" required>
                        @foreach(['Diterima', 'Dalam Proses', 'Menunggu Komponen', 'Selesai'] as $statusOption)
                        <option value="{{ $statusOption }}" {{ old('status', $maintenance->status) === $statusOption ? 'selected' : '' }}>
                            {{ $statusOption }}
                        </option>
                        @endforeach
                    </select>
                    @error('status')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="cost">Biaya (Rp)</label>
                    <input type="number" id="cost" name="cost" class="form-control @error('cost') error @enderror"
                        value="{{ old('cost', $maintenance->cost) }}" min="0">
                    @error('cost')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="technician">Nama Teknisi / Pelaksana</label>
                    <input type="text" id="technician" name="technician" class="form-control"
                        value="{{ old('technician', $maintenance->technician) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Catatan Pengerjaan</label>
                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $maintenance->description) }}</textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-success">Update Status</button>
            </div>
        </form>
        @endcan
        @endif
    </div>
</div>
@endsection