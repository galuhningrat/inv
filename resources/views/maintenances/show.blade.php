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
        <div class="detail-row" style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="detail-image" style="flex: 0 0 200px;">
                <img src="{{ $maintenance->asset->image_url }}" alt="{{ $maintenance->asset->name }}"
                    style="width: 100%; border-radius: 8px; object-fit: cover;">
            </div>
            <div class="detail-info" style="flex: 1;">
                <p><strong>Aset:</strong> {{ $maintenance->asset->name }} ({{ $maintenance->asset->asset_id }})</p>
                <p><strong>Jenis Pemeliharaan:</strong> {{ $maintenance->type }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $maintenance->maintenance_date->format('d F Y') }}</p>
                <p><strong>Dilaporkan oleh:</strong> {{ $maintenance->recorder->name ?? '-' }}</p>
                <p><strong>Keterangan Awal:</strong> {{ $maintenance->description }}</p>
            </div>
        </div>

        <hr style="margin: 1.5rem 0;">

        <h4 style="margin-bottom: 1rem;">Update Status Tiket</h4>

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
                    <label for="cost">Biaya</label>
                    <input type="number" id="cost" name="cost" class="form-control @error('cost') error @enderror"
                        value="{{ old('cost', $maintenance->cost) }}" min="0">
                    @error('cost')
                    <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="technician">Nama Teknisi</label>
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
    </div>
</div>
@endsection