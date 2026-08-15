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
            <p><strong>Diajukan oleh:</strong> {{ $assetRequest->requester->name }}</p>
            <p><strong>Total Item:</strong> {{ $assetRequest->items->count() }} jenis barang,
                {{ $assetRequest->items->sum('quantity') }} unit</p>

            <hr style="margin: 1.5rem 0;">

            <form action="{{ route('requests.receive', $assetRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h4 style="margin-bottom: 1rem;">Data Umum</h4>
                @if ($assetRequest->alasan_pengajuan === 'Penggantian' && $assetRequest->relatedAsset)
                    <div
                        style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <span style="font-size: 1.5rem;">⚠️</span>
                            <div>
                                <p style="margin: 0; font-weight: 600; color: #92400e;">Pengajuan Penggantian Aset</p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.9rem; color: #78350f;">
                                    Aset yang akan diganti:
                                    <strong>{{ $assetRequest->relatedAsset->asset_id }} —
                                        {{ $assetRequest->relatedAsset->name }}</strong>
                                    <br>
                                    <small style="color: #92400e;">
                                        🔄 Setelah registrasi selesai, aset lama akan otomatis berstatus
                                        <strong>"Diganti"</strong>
                                        dan tidak bisa dipinjam lagi.
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_date">Tanggal Terima <span style="color:red;">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date"
                            class="form-control @error('purchase_date') error @enderror"
                            value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="penanggung_jawab_id">Penanggung Jawab</label>
                        <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($usersByLevel as $level => $usersInLevel)
                                <optgroup label="{{ $level }}">
                                    @foreach ($usersInLevel as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('penanggung_jawab_id', $assetRequest->requester_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($hasPhysical)
                    <div id="physicalLocationBlock">
                        <h4 style="margin: 1.5rem 0 1rem;">Lokasi Penempatan (untuk item fisik)</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori">Kategori <span style="color: red;">*</span></label>
                                <select id="kategori" class="form-control" required onchange="updateUnitOptions()">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($units->pluck('category')->unique() as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unit_id">Unit <span style="color: red;">*</span></label>
                                <select id="unit_id" name="unit_id" class="form-control @error('unit_id') error @enderror"
                                    required disabled onchange="updateLocationOptions()">
                                    <option value="">-- Pilih Kategori dulu --</option>
                                </select>
                                @error('unit_id')
                                    <div class="error-message" style="display: block;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="location_id">Lokasi Spesifik</label>
                            <select id="location_id" name="location_id"
                                class="form-control @error('location_id') error @enderror" disabled>
                                <option value="">-- Pilih Unit dulu --</option>
                            </select>
                            @error('location_id')
                                <div class="error-message" style="display: block;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label id="location_detail_label" for="location_detail">Detail Penempatan <small
                                    style="font-weight: normal;">(opsional)</small></label>
                            <input type="text" id="location_detail" name="location_detail" class="form-control"
                                value="{{ old('location_detail') }}">
                        </div>
                    </div>

                    <script id="unitsData" type="application/json">
                        @json($unitsForJs)
                    </script>
                @endif

                <hr style="margin: 1.5rem 0;">

                @foreach ($assetRequest->items as $item)
                    <div
                        style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.25rem;">
                            {{ $item->item_type === 'Fisik' ? '📦' : '💾' }} {{ $item->item_name }}
                            ({{ $item->quantity }} {{ $item->unit }})
                        </h4>

                        {{-- ========================================================= --}}
                        {{--  PENJELASAN JENIS ASET (FISIK vs NON-FISIK)              --}}
                        {{-- ========================================================= --}}
                        @if ($item->item_type === 'Fisik')
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                                📦 Item ini akan otomatis terdaftar sebagai <strong>Aset Fisik</strong> di Manajemen Aset,
                                sesuai kategori yang dipilih saat pengajuan.
                            </p>
                        @else
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                                💾 Item ini akan otomatis terdaftar sebagai <strong>Aset Non-Fisik</strong>,
                                sesuai kategori yang dipilih saat pengajuan — tidak perlu input ulang jenisnya.
                            </p>
                        @endif

                        @if ($item->specification)
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">Spesifikasi:
                                {{ $item->specification }}</p>
                        @endif

                        @if ($item->item_type === 'Fisik')
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Merek <span style="color:red;">*</span></label>
                                    <input type="text" name="brand[{{ $item->id }}]"
                                        class="form-control @error('brand.' . $item->id) error @enderror"
                                        value="{{ old('brand.' . $item->id) }}" required>
                                    @error('brand.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Harga per Unit (Rp) <span style="color:red;">*</span></label>
                                    <input type="number" name="prices[{{ $item->id }}]"
                                        class="form-control @error('prices.' . $item->id) error @enderror"
                                        value="{{ old('prices.' . $item->id, $item->estimated_price_per_unit) }}"
                                        min="0" required>
                                    @error('prices.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <p style="font-weight: 600; margin: 1rem 0 0.5rem;">Detail Per Unit:</p>
                            @for ($i = 0; $i < $item->quantity; $i++)
                                <div
                                    style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <p style="font-weight: 600; margin-bottom: 0.75rem;">Unit #{{ $i + 1 }}</p>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Nama Unit <small style="font-weight: normal;">(opsional)</small></label>
                                            <input type="text" name="unit_names[{{ $item->id }}][]"
                                                class="form-control" placeholder="{{ $item->item_name }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Foto Unit <span style="color:red;">*</span></label>
                                            <input type="file" name="images[{{ $item->id }}][]"
                                                class="form-control @error('images.' . $item->id . '.' . $i) error @enderror"
                                                accept="image/*" required>
                                            @error('images.' . $item->id . '.' . $i)
                                                <div class="error-message" style="display:block;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Nomor Seri / Kode Unit <span style="color:red;">*</span></label>
                                            <input type="text" name="serial_numbers[{{ $item->id }}][]"
                                                class="form-control @error('serial_numbers.' . $item->id . '.' . $i) error @enderror"
                                                required>
                                            @error('serial_numbers.' . $item->id . '.' . $i)
                                                <div class="error-message" style="display:block;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Kondisi <span style="color:red;">*</span></label>
                                            <select name="conditions[{{ $item->id }}][]" class="form-control"
                                                required>
                                                <option value="Baik">Baik</option>
                                                <option value="Rusak Ringan">Rusak Ringan</option>
                                                <option value="Rusak Berat">Rusak Berat</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Batas Akhir Masa/Expired <small
                                                    style="font-weight: normal;">(opsional)</small></label>
                                            <input type="date" name="expired_dates[{{ $item->id }}][]"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @else
                            {{-- Non-Fisik --}}
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kategori <span style="color:red;">*</span></label>
                                    <select name="categories[{{ $item->id }}]"
                                        class="form-control @error('categories.' . $item->id) error @enderror" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach (['Software' => 'Perangkat Lunak / Software', 'HAKI/Paten' => 'HAKI / Paten', 'Jurnal Ilmiah' => 'Jurnal Ilmiah', 'Domain/Hosting' => 'Domain & Hosting', 'Kurikulum' => 'Lisensi Kurikulum'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('categories.' . $item->id) === $val ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('categories.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Vendor / Penyedia <span style="color:red;">*</span></label>
                                    <input type="text" name="vendors[{{ $item->id }}]"
                                        class="form-control @error('vendors.' . $item->id) error @enderror"
                                        value="{{ old('vendors.' . $item->id) }}" required>
                                    @error('vendors.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Harga per Unit (Rp) <span style="color:red;">*</span></label>
                                    <input type="number" name="prices[{{ $item->id }}]"
                                        class="form-control @error('prices.' . $item->id) error @enderror"
                                        value="{{ old('prices.' . $item->id, $item->estimated_price_per_unit) }}"
                                        min="0" required>
                                    @error('prices.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Sumber Pendanaan</label>
                                    <input type="text" name="funding_sources[{{ $item->id }}]"
                                        class="form-control" value="{{ old('funding_sources.' . $item->id) }}">
                                </div>
                                <div class="form-group">
                                    <label>Nomor Kontrak / Sertifikat / SK</label>
                                    <input type="text" name="contract_numbers[{{ $item->id }}]"
                                        class="form-control" value="{{ old('contract_numbers.' . $item->id) }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jenis Lisensi <span style="color:red;">*</span></label>
                                    <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
                                        <label style="font-weight: normal;"><input type="radio"
                                                name="license_types[{{ $item->id }}]" value="Berlangganan"
                                                onchange="toggleExpiry({{ $item->id }})" required>
                                            Berlangganan</label>
                                        <label style="font-weight: normal;"><input type="radio"
                                                name="license_types[{{ $item->id }}]" value="Selamanya"
                                                onchange="toggleExpiry({{ $item->id }})"> Selamanya</label>
                                    </div>
                                    @error('license_types.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group" id="expiryGroup_{{ $item->id }}" style="display: none;">
                                    <label>Tanggal Kedaluwarsa</label>
                                    <input type="date" name="expiry_dates[{{ $item->id }}]"
                                        class="form-control @error('expiry_dates.' . $item->id) error @enderror">
                                    @error('expiry_dates.' . $item->id)
                                        <div class="error-message" style="display:block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>URL Akses / Dasbor Admin</label>
                                    <input type="url" name="access_urls[{{ $item->id }}]" class="form-control"
                                        value="{{ old('access_urls.' . $item->id) }}" placeholder="https://...">
                                </div>
                                <div class="form-group">
                                    <label>File Sertifikat / Bukti Kepemilikan</label>
                                    <input type="file" name="certificate_files[{{ $item->id }}]"
                                        class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>

                            <p style="font-weight: 600; margin: 1rem 0 0.5rem;">Detail Per Unit (Product Key / Akun):</p>
                            @for ($i = 0; $i < $item->quantity; $i++)
                                <div
                                    style="border: 1px dashed var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <p style="font-weight: 600; margin-bottom: 0.75rem;">Unit #{{ $i + 1 }}</p>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Product / Activation Key <small
                                                    style="font-weight: normal;">(opsional)</small></label>
                                            <input type="text" name="product_keys[{{ $item->id }}][]"
                                                class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Email Pengguna <small style="font-weight: normal;">(opsional, untuk
                                                    lisensi named-user)</small></label>
                                            <input type="email" name="assigned_emails[{{ $item->id }}][]"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        @endif
                    </div>
                @endforeach

                <div class="btn-group">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Terima &amp; Daftarkan Semua ke Inventaris</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if ($hasPhysical)
            const allUnits = JSON.parse(document.getElementById('unitsData').textContent);

            function updateUnitOptions() {
                const kategori = document.getElementById('kategori').value;
                const unitSelect = document.getElementById('unit_id');
                const locationSelect = document.getElementById('location_id');
                unitSelect.innerHTML = '<option value="">Pilih Unit</option>';
                locationSelect.innerHTML = '<option value="">-- Pilih Unit dulu --</option>';
                locationSelect.disabled = true;
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
                const detailLabel = document.getElementById('location_detail_label');
                locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';
                if (!unitId) {
                    locationSelect.disabled = true;
                    locationSelect.required = false;
                    return;
                }
                const unit = allUnits.find(u => u.id === unitId);
                if (!unit || unit.locations.length === 0) {
                    locationSelect.innerHTML = '<option value="">-- Tidak ada lokasi spesifik --</option>';
                    locationSelect.disabled = true;
                    locationSelect.required = false;
                    locationDetail.required = true;
                    detailLabel.innerHTML =
                        'Detail Penempatan <span style="color:red;">*</span> <small style="font-weight: normal;">(wajib, unit ini belum punya lokasi spesifik)</small>';
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
                    detailLabel.innerHTML = 'Detail Penempatan <small style="font-weight: normal;">(opsional)</small>';
                }
            }
        @endif

        function toggleExpiry(itemId) {
            const checked = document.querySelector(`input[name="license_types[${itemId}]"]:checked`);
            const isSubscription = checked && checked.value === 'Berlangganan';
            const group = document.getElementById(`expiryGroup_${itemId}`);
            group.style.display = isSubscription ? 'block' : 'none';
            group.querySelector('input').required = isSubscription;
        }
    </script>
@endpush
