@extends('layouts.app')

@section('title', 'Detail Grup Aset')
@section('page-title', 'Detail Grup: ' . $groupName)

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">{{ $groupName }} ({{ $groupBrand }}) — {{ $units->count() }} Unit</h3>
            <a href="{{ route('assets-inv.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Aset</th>
                        <th>Gambar</th>
                        <th>Nomor Seri</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td><strong>{{ $unit->asset_id }}</strong></td>
                            <td><img src="{{ $unit->image_url }}" alt="{{ $unit->name }}"
                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;"></td>
                            <td>{{ $unit->serial_number }}</td>
                            <td>{{ $unit->location }}</td>
                            <td>
                                <span
                                    class="status-badge {{ $unit->condition === 'Baik' ? 'available' : ($unit->condition === 'Rusak Ringan' ? 'borrowed' : 'maintenance') }}">{{ $unit->condition }}</span>
                            </td>
                            <td>
                                <span
                                    class="status-badge {{ $unit->status === 'Tersedia' ? 'available' : ($unit->status === 'Dipinjam' ? 'borrowed' : 'maintenance') }}">{{ $unit->status }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @php
                                        $returnUrl = route('assets-inv.group-detail', [
                                            'name' => $groupName,
                                            'type_id' => $unit->asset_type_id,
                                            'brand' => $groupBrand,
                                        ]);
                                    @endphp

                                    <a href="{{ route('assets-inv.show', ['asset' => $unit->id, 'group_name' => $groupName, 'group_type_id' => $unit->asset_type_id, 'group_brand' => $groupBrand]) }}"
                                        class="btn btn-secondary">Detail</a>
                                    <a href="{{ route('assets-inv.edit', ['asset' => $unit->id, 'group_name' => $groupName, 'group_type_id' => $unit->asset_type_id, 'group_brand' => $groupBrand]) }}"
                                        class="btn btn-primary">Edit</a>
                                    @can('delete', $unit)
                                        <form action="{{ route('assets-inv.destroy', $unit) }}" method="POST"
                                            style="display: inline;" onsubmit="return confirm('Hapus unit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="group_name" value="{{ $groupName }}">
                                            <input type="hidden" name="group_type_id" value="{{ $unit->asset_type_id }}">
                                            <input type="hidden" name="group_brand" value="{{ $groupBrand }}">
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
