@extends('layouts.app')

@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Terima Barang: {{ $assetRequest->request_id }}</h3>
        <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>

    <div style="padding: 2rem;">

        {{-- ============================================================ --}}
        {{-- BAGIAN A — INFORMASI PENGAJUAN --}}
        {{-- ============================================================ --}}
        <div style="background: var(--light-bg); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; border-left: 4px solid var(--primary-color);">
            <h4 style="margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">A. Informasi Pengajuan</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <p style="margin: 0.25rem 0;"><strong>Nomor Pengajuan:</strong> {{ $assetRequest->request_id }}</p>
                    <p style="margin: 0.25rem 0;"><strong>Tanggal Pengajuan:</strong> {{ $assetRequest->created_at->format('d F Y H:i') }}</p>
                    <p style="margin: 0.25rem 0;"><strong>Pemohon:</strong> {{ $assetRequest->requester->name }}</p>
                </div>
                <div>
                    <p style="margin: 0.25rem 0;"><strong>Unit Pengaju:</strong> {{ $assetRequest->unit->name ?? '-' }}</p>
                    <p style="margin: 0.25rem 0;"><strong>Jenis Pengajuan:</strong>
                        <span class="status-badge {{ $assetRequest->request_type === 'jasa' ? 'borrowed' : 'available' }}">
                            {{ $assetRequest->request_type === 'jasa' ? '🛠️ Jasa' : '📦 Aset' }}
                        </span>
                    </p>
                    <p style="margin: 0.25rem 0;"><strong>Status Workflow:</strong> 
                        <span class="status-badge {{ $assetRequest->status === 'Dikonfirmasi' ? 'available' : 'borrowed' }}">{{ $assetRequest->status }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BAGIAN B — DAFTAR ITEM --}}
        {{-- ============================================================ --}}
        <div style="margin-bottom: 2rem;">
            <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">B. Daftar Item Pengajuan</h4>
            <div class="table-wrapper" style="border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color);">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Item</th>
                            <th>Kuantitas</th>
                            <th>Estimasi Harga</th>
                            <th>Jenis Penerimaan</th>
                            <th>Status Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Aset Items --}}
                        @foreach ($assetRequest->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->item_name }}</strong><br>
                                <small style="color: var(--text-secondary);">{{ $item->specification ?? '-' }}</small>
                            </td>
                            <td>{{ $item->quantity }} {{ $item->unit }}</td>
                            <td>Rp {{ number_format($item->estimated_price_per_unit, 0, ',', '.') }}</td>
                            <td>
                                @if($item->isLightReceipt())
                                    <span class="status-badge pending">Light Receipt</span>
                                @elseif($item->item_type === 'Fisik')
                                    <span class="status-badge available">Aset Fisik</span>
                                @else
                                    <span class="status-badge borrowed">Aset Non-Fisik</span>
                                @endif
                            </td>
                            <td><span class="status-badge {{ $item->approval_badge_class }}">{{ $item->approval_status_label }}</span></td>
                        </tr>
                        @endforeach
                        
                        {{-- Jasa Items (Display Only) --}}
                        @foreach ($assetRequest->serviceItems as $sItem)
                        <tr>
                            <td><strong>{{ $sItem->item_name }}</strong><br><small style="color: var(--text-secondary);">{{ $sItem->specification ?? '-' }}</small></td>
                            <td>{{ $sItem->quantity }} {{ $sItem->unit }}</td>
                            <td>Rp {{ number_format($sItem->estimated_price_per_unit, 0, ',', '.') }}</td>
                            <td><span class="status-badge borrowed">Jasa</span></td>
                            <td><span class="status-badge {{ $sItem->approval_badge_class }}">{{ $sItem->approval_status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($assetRequest->serviceItems->isNotEmpty())
            <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #6d28d9;"><em>* Item Jasa diselesaikan melalui menu "Selesaikan Jasa", tidak diregistrasi di form ini.</em></p>
            @endif
        </div>

        <form action="{{ route('requests.receive', $assetRequest) }}" method="POST" enctype="multipart/form-data" id="receiveForm">
            @csrf

            {{-- ============================================================ --}}
            {{-- BAGIAN C — DATA UMUM PENERIMAAN --}}
            {{-- ============================================================ --}}
            <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h4 style="margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    C. Data Umum Penerimaan
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Tanggal Terima <span style="color:red;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-control @error('purchase_date') error @enderror" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date') <div class="error-message" style="display:block;">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="penanggung_jawab_id">Penanggung Jawab <span style="color:red;">*</span></label>
                        <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control @error('penanggung_jawab_id') error @enderror" required>
                            <option value="">-- Pilih Penanggung Jawab --</option>
                            @foreach ($usersByLevel as $level => $usersInLevel)
                            <optgroup label="{{ $level }}">
                                @foreach ($usersInLevel as $u)
                                <option value="{{ $u->id }}" {{ old('penanggung_jawab_id', $assetRequest->requester_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        @error('penanggung_jawab_id') <div class="error-message" style="display:block;">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if ($hasPhysical)
                <div id="physicalLocationBlock" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                    <h5 style="margin-bottom: 1rem; color: var(--text-secondary);">📍 Penempatan Aset Fisik</h5>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori">Kategori Unit <span style="color: red;">*</span></label>
                            <select id="kategori" class="form-control" required onchange="updateUnitOptions()">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($units->pluck('category')->unique()->filter() as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit_id">Unit Penempatan <span style="color: red;">*</span></label>
                            <select id="unit_id" name="unit_id" class="form-control @error('unit_id') error @enderror" required disabled onchange="updateLocationOptions()">
                                <option value="">-- Pilih Kategori dulu --</option>
                            </select>
                            @error('unit_id') <div class="error-message" style="display: block;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="location_id">Lokasi Spesifik <span id="label_location_id" style="color: red;">*</span></label>
                            <select id="location_id" name="location_id" class="form-control @error('location_id') error @enderror" disabled>
                                <option value="">-- Pilih Unit dulu --</option>
                            </select>
                            @error('location_id') <div class="error-message" style="display: block;">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label id="location_detail_label" for="location_detail">Detail Penempatan <span id="label_location_detail" style="color: red;">*</span></label>
                            <input type="text" id="location_detail" name="location_detail" class="form-control @error('location_detail') error @enderror" value="{{ old('location_detail') }}" placeholder="Contoh: Meja Operator, Rak Server, Sisi Kanan Ruangan">
                            @error('location_detail') <div class="error-message" style="display: block;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <script id="unitsData" type="application/json">@json($unitsForJs)</script>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- BAGIAN D — DETAIL PENERIMAAN (PER ITEM) --}}
            {{-- ============================================================ --}}
            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">D. Detail Penerimaan</h4>
                
                @foreach ($assetRequest->items as $item)
                    @if($item->isLightReceipt() || $item->item_type === 'Fisik' || $item->item_type === 'Non-Fisik')
                    <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; position: relative; background: #fff;">
                        
                        <h4 style="margin-bottom: 0.25rem; padding-right: 150px;">
                            {{ $item->item_type === 'Fisik' ? '📦' : '💾' }} {{ $item->item_name }}
                            ({{ $item->quantity }} {{ $item->unit }})
                        </h4>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; padding-right: 150px;">
                            Prioritas: <strong>{{ $item->priority }}</strong> | Estimasi: Rp {{ number_format($item->estimated_price_per_unit, 0, ',', '.') }}
                        </p>

                        {{-- 1. LIGHT RECEIPT --}}
                        @if ($item->isLightReceipt())
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div class="form-row">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>Informasi Kuantitas</label>
                                    <p style="margin: 0; font-size: 0.9rem;">
                                        Diajukan: <strong>{{ $item->quantity }} {{ $item->unit }}</strong> <br>
                                        Sudah Diterima: <strong>{{ $item->received_quantity ?? 0 }} {{ $item->unit }}</strong> <br>
                                        Sisa: <strong style="color: #dc2626;">{{ $item->quantity - ($item->received_quantity ?? 0) }} {{ $item->unit }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jumlah Diterima (Realisasi Kali Ini) <span style="color:red;">*</span></label>
                                <input type="number" name="received_quantities[{{ $item->id }}]" class="form-control summary-qty" data-item-id="{{ $item->id }}" value="{{ old('received_quantities.' . $item->id, $item->quantity - ($item->received_quantity ?? 0)) }}" min="0" max="{{ $item->quantity - ($item->received_quantity ?? 0) }}" required>
                                @error('received_quantities.' . $item->id) <div class="error-message" style="display:block;">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Catatan <small style="font-weight: normal;">(opsional)</small></label>
                                <textarea name="receipt_notes[{{ $item->id }}]" class="form-control" rows="2" placeholder="Contoh: diterima dalam kondisi baik / BAST terlampir">{{ old('receipt_notes.' . $item->id) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Bukti Penerimaan / Nota / BAST <small style="font-weight: normal;">(opsional)</small></label>
                                <input type="file" name="receipt_proof_files[{{ $item->id }}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        {{-- 2. ASET FISIK --}}
                        @elseif ($item->item_type === 'Fisik')
                        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <div class="form-row">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>Jumlah Aktual Diterima <span style="color:red;">*</span></label>
                                    <input type="number" id="received_qty_{{ $item->id }}" name="received_quantities[{{ $item->id }}]" class="form-control summary-qty" data-item-id="{{ $item->id }}" value="{{ old('received_quantities.' . $item->id, $item->quantity) }}" min="1" max="{{ $item->quantity }}" oninput="toggleUnitForms({{ $item->id }}, {{ $item->quantity }})" required>
                                    <small style="color: #991b1b;">Diajukan: {{ $item->quantity }} {{ $item->unit }}. Ubah jika jumlah riil berbeda.</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Merek Aktual <span style="color:red;">*</span></label>
                                <input type="text" name="brand[{{ $item->id }}]" class="form-control" value="{{ old('brand.' . $item->id) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Model <small style="font-weight: normal;">(opsional)</small></label>
                                <input type="text" name="model[{{ $item->id }}]" class="form-control" value="{{ old('model.' . $item->id, $item->specification) }}">
                            </div>
                            <div class="form-group">
                                <label>Harga Aktual per Unit (Rp) <span style="color:red;">*</span></label>
                                <input type="number" name="prices[{{ $item->id }}]" class="form-control" value="{{ old('prices.' . $item->id, $item->estimated_price_per_unit) }}" min="0" required>
                            </div>
                        </div>
                        <p style="font-weight: 600; margin: 1rem 0 0.5rem;">Detail Identitas Per Unit:</p>
                        @for ($i = 0; $i < $item->quantity; $i++)
                            <div id="unit_block_{{ $item->id }}_{{ $i }}" class="unit-block" style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem; display: {{ $i < $item->quantity ? 'block' : 'none' }};">
                                <p style="font-weight: 600; margin-bottom: 0.75rem;">Unit #{{ $i + 1 }}</p>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nama Spesifik Unit <small style="font-weight: normal;">(opsional)</small></label>
                                        <input type="text" name="unit_names[{{ $item->id }}][{{ $i }}]" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Foto Unit <span style="color:red;">*</span></label>
                                        <input type="file" name="images[{{ $item->id }}][{{ $i }}]" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_{{ $item->id }}_{{ $i }}')" required>
                                        <div id="preview_{{ $item->id }}_{{ $i }}" style="margin-top: 0.5rem; display: none;"><img src="" style="max-height: 100px; border-radius: 6px; border: 1px solid #ddd;"></div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Serial Number <span style="color:red;">*</span></label>
                                        <input type="text" name="serial_numbers[{{ $item->id }}][{{ $i }}]" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Kondisi <span style="color:red;">*</span></label>
                                        <select name="conditions[{{ $item->id }}][{{ $i }}]" class="form-control" required>
                                            <option value="Baik">Baik</option>
                                            <option value="Rusak Ringan">Rusak Ringan</option>
                                            <option value="Rusak Berat">Rusak Berat</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Garansi / Expired <small style="font-weight: normal;">(opsional)</small></label>
                                        <input type="date" name="expired_dates[{{ $item->id }}][{{ $i }}]" class="form-control">
                                    </div>
                                </div>
                            </div>
                        @endfor

                        {{-- 3. ASET NON-FISIK --}}
                        @else
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kategori <span style="color:red;">*</span></label>
                                <select name="categories[{{ $item->id }}]" class="form-control" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $val => $label)
                                    <option value="{{ $val }}" {{ old('categories.' . $item->id, $item->category) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vendor / Penyedia <span style="color:red;">*</span></label>
                                <input type="text" name="vendors[{{ $item->id }}]" class="form-control" value="{{ old('vendors.' . $item->id) }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Harga per Unit (Rp) <span style="color:red;">*</span></label>
                                <input type="number" name="prices[{{ $item->id }}]" class="form-control" value="{{ old('prices.' . $item->id, $item->estimated_price_per_unit) }}" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Sumber Pendanaan</label>
                                <input type="text" name="funding_sources[{{ $item->id }}]" class="form-control" value="{{ old('funding_sources.' . $item->id) }}">
                            </div>
                            <div class="form-group">
                                <label>Nomor Kontrak / SK</label>
                                <input type="text" name="contract_numbers[{{ $item->id }}]" class="form-control" value="{{ old('contract_numbers.' . $item->id) }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Lisensi <span style="color:red;">*</span></label>
                                <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
                                    <label style="font-weight: normal;"><input type="radio" name="license_types[{{ $item->id }}]" value="Berlangganan" onchange="toggleExpiry({{ $item->id }})" required> Berlangganan</label>
                                    <label style="font-weight: normal;"><input type="radio" name="license_types[{{ $item->id }}]" value="Selamanya" onchange="toggleExpiry({{ $item->id }})"> Selamanya</label>
                                </div>
                            </div>
                            <div class="form-group" id="expiryGroup_{{ $item->id }}" style="display: none;">
                                <label>Tanggal Kedaluwarsa</label>
                                <input type="date" name="expiry_dates[{{ $item->id }}]" class="form-control">
                            </div>
                            <div class="form-group" id="reminderGroup_{{ $item->id }}" style="display: none;">
                                <label>Pengingat</label>
                                <select name="reminder_days[{{ $item->id }}]" class="form-control">
                                    @foreach ($reminderOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>URL Akses / Dasbor Admin</label>
                                <input type="url" name="access_urls[{{ $item->id }}]" class="form-control" placeholder="https://...">
                            </div>
                            <div class="form-group">
                                <label>File Sertifikat / Kontrak</label>
                                <input type="file" name="certificate_files[{{ $item->id }}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <p style="font-weight: 600; margin: 1rem 0 0.5rem;">Detail Per Unit (Product Key / Akun):</p>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
                            <p style="margin: 0; font-size: 0.8rem; color: #991b1b;">🔒 <strong>Informasi Sensitif:</strong> Product Key dienkripsi di database.</p>
                        </div>
                        @for ($i = 0; $i < $item->quantity; $i++)
                            <div style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                <p style="font-weight: 600; margin-bottom: 0.75rem;">Unit #{{ $i + 1 }}</p>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Product Key <small style="font-weight: normal;">(opsional)</small></label>
                                        <input type="text" name="product_keys[{{ $item->id }}][{{ $i }}]" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Pengguna <small style="font-weight: normal;">(opsional)</small></label>
                                        <input type="email" name="assigned_emails[{{ $item->id }}][{{ $i }}]" class="form-control">
                                    </div>
                                </div>
                            </div>
                        @endfor
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>

            {{-- ============================================================ --}}
            {{-- BAGIAN E — RINGKASAN DAN KONFIRMASI --}}
            {{-- ============================================================ --}}
            <div style="background: #eff6ff; border: 2px solid #3b82f6; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <h4 style="margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid #bfdbfe; padding-bottom: 0.5rem; color: #1e40af;">E. Ringkasan dan Konfirmasi</h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background: #fff; padding: 1rem; border-radius: 6px; border: 1px solid #bfdbfe;">
                        <small style="color: #6b7280;">Total Item Aset Fisik</small>
                        <h3 style="margin: 0.25rem 0 0; color: #1e40af;" id="summary_total_fisik">{{ $assetRequest->items->where('item_type', 'Fisik')->where('sifat_barang', '!=', 'Habis Pakai')->sum('quantity') }} Unit</h3>
                    </div>
                    <div style="background: #fff; padding: 1rem; border-radius: 6px; border: 1px solid #bfdbfe;">
                        <small style="color: #6b7280;">Total Item Non-Fisik</small>
                        <h3 style="margin: 0.25rem 0 0; color: #1e40af;" id="summary_total_nonfisik">{{ $assetRequest->items->where('item_type', 'Non-Fisik')->sum('quantity') }} Lisensi</h3>
                    </div>
                    <div style="background: #fff; padding: 1rem; border-radius: 6px; border: 1px solid #bfdbfe;">
                        <small style="color: #6b7280;">Total Light Receipt (Habis Pakai)</small>
                        <h3 style="margin: 0.25rem 0 0; color: #1e40af;" id="summary_total_lightreceipt">{{ $assetRequest->items->filter(fn($i) => $i->isLightReceipt())->sum('quantity') }} Pcs</h3>
                    </div>
                </div>

                <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <p style="margin: 0; font-size: 0.9rem; color: #92400e;">
                        ⚠️ <strong>Perhatian:</strong> Pastikan semua field wajib (bertanda <span style="color:red;">*</span>) seperti Foto Unit, Serial Number, dan Merek sudah terisi sebelum menyimpan. Data yang disimpan tidak dapat dibatalkan.
                    </p>
                </div>

                <div class="btn-group" style="justify-content: flex-end;">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success" id="btnSubmit" style="min-width: 200px; font-weight: 600;" onclick="this.innerHTML = '⏳ Memproses...'; this.disabled = true; this.form.submit();">
                        ✅ Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 🔥 UI/UX: Loading State & Prevent Double Submit (Poin 16.2)
    document.getElementById('receiveForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '⏳ Memproses...';
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
    });

    // ============================================================
    // LOGIKA CASCADING DROPDOWN (KATEGORI -> UNIT -> LOKASI)
    // ============================================================
    @if($hasPhysical)
    const unitsDataEl = document.getElementById('unitsData');
    const allUnits = unitsDataEl ? JSON.parse(unitsDataEl.textContent) : [];

    function updateUnitOptions() {
        const kategori = document.getElementById('kategori').value;
        const unitSelect = document.getElementById('unit_id');
        const locationSelect = document.getElementById('location_id');
        const locationDetail = document.getElementById('location_detail');

        unitSelect.innerHTML = '<option value="">Pilih Unit</option>';
        locationSelect.innerHTML = '<option value="">-- Pilih Unit dulu --</option>';
        locationSelect.disabled = true;
        locationSelect.required = false;
        locationDetail.required = false;

        if (!kategori) {
            unitSelect.disabled = true;
            unitSelect.innerHTML = '<option value="">-- Pilih Kategori dulu --</option>';
            return;
        }
        allUnits.filter(u => u.category === kategori).forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.name;
            unitSelect.appendChild(opt);
        });
        unitSelect.disabled = false;
    }

    function updateLocationOptions() {
        const unitId = parseInt(document.getElementById('unit_id').value);
        const locationSelect = document.getElementById('location_id');
        const locationDetail = document.getElementById('location_detail');
        const labelLocationId = document.getElementById('label_location_id');
        const labelLocationDetail = document.getElementById('label_location_detail');

        locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';
        locationDetail.value = '';

        if (!unitId) {
            locationSelect.disabled = true;
            return;
        }

        const unit = allUnits.find(u => u.id === unitId);

        if (!unit || unit.locations.length === 0) {
            locationSelect.innerHTML = '<option value="">-- Tidak ada lokasi spesifik --</option>';
            locationSelect.disabled = true;
            locationSelect.required = false;
            locationDetail.required = true;
            labelLocationId.style.display = 'none';
            labelLocationDetail.style.display = 'inline';
        } else {
            unit.locations.forEach(loc => {
                const opt = document.createElement('option');
                opt.value = loc.id;
                opt.textContent = loc.name;
                locationSelect.appendChild(opt);
            });
            locationSelect.disabled = false;
            locationSelect.required = true;
            locationDetail.required = false;
            labelLocationId.style.display = 'inline';
            labelLocationDetail.style.display = 'none';
        }
    }
    @endif

    // ============================================================
    // LOGIKA TOGGLE TANGGAL KEDALUWARSA
    // ============================================================
    function toggleExpiry(itemId) {
        const checked = document.querySelector(`input[name="license_types[${itemId}]"]:checked`);
        const isSubscription = checked && checked.value === 'Berlangganan';
        const group = document.getElementById(`expiryGroup_${itemId}`);
        group.style.display = isSubscription ? 'block' : 'none';
        group.querySelector('input').required = isSubscription;

        const reminderGroup = document.getElementById(`reminderGroup_${itemId}`);
        if (reminderGroup) {
            reminderGroup.style.display = isSubscription ? 'block' : 'none';
            if (!isSubscription) reminderGroup.querySelector('select').value = '';
        }
    }

    // ============================================================
    // LOGIKA TOGGLE UNIT FISIK (PENERIMAAN PARSIAL)
    // ============================================================
    function toggleUnitForms(itemId, maxQty) {
        const receivedQty = parseInt(document.getElementById(`received_qty_${itemId}`).value) || 0;
        for (let i = 0; i < maxQty; i++) {
            const block = document.getElementById(`unit_block_${itemId}_${i}`);
            if (block) {
                if (i < receivedQty) {
                    block.style.display = 'block';
                    block.querySelectorAll('input, select').forEach(el => {
                        if (el.name.includes('images') || el.name.includes('serial_numbers') || el.name.includes('conditions')) {
                            el.required = true;
                        }
                    });
                } else {
                    block.style.display = 'none';
                    block.querySelectorAll('input, select').forEach(el => el.required = false);
                }
            }
        }
    }

    // ============================================================
    // PREVIEW FOTO UNIT
    // ============================================================
    function previewImage(input, previewId) {
        const previewContainer = document.getElementById(previewId);
        const img = previewContainer.querySelector('img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            img.src = '';
            previewContainer.style.display = 'none';
        }
    }

    // Inisialisasi awal saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="radio"][name^="license_types"]').forEach(radio => {
            if (radio.checked) {
                const itemId = radio.name.match(/\[(\d+)\]/)[1];
                toggleExpiry(itemId);
            }
        });
    });
</script>
@endpush