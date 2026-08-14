@extends('layouts.app')

@section('title', 'Ajukan Aset')
@section('page-title', 'Ajukan Aset Baru')

@section('content')
    {{-- ============================================================ --}}
    {{--  CSS UNTUK KARTU PILIHAN YANG AESTHETIC                      --}}
    {{-- ============================================================ --}}
    <style>
        /* =============================================
                           ITEM TYPE PICKER – VERSION 2 (AESTHETIC)
                           ============================================= */
        .item-type-picker {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .item-type-option {
            flex: 1;
            min-width: 240px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: var(--card-background, #ffffff);
            border: 2px solid var(--border-color, #e5e7eb);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            user-select: none;
        }

        .item-type-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color, #2563eb);
        }

        .item-type-option.active {
            border-color: var(--primary-color, #2563eb);
            background: color-mix(in srgb, var(--primary-color, #2563eb) 6%, var(--card-background, #ffffff));
            box-shadow: 0 8px 28px rgba(37, 99, 235, 0.15);
        }

        .item-type-option .item-type-icon {
            font-size: 2.5rem;
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg, #f3f4f6);
            border-radius: 12px;
            transition: background 0.3s ease;
        }

        .item-type-option.active .item-type-icon {
            background: color-mix(in srgb, var(--primary-color, #2563eb) 12%, #ffffff);
        }

        .item-type-option .item-type-text {
            flex: 1;
        }

        .item-type-option .item-type-text strong {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary, #111827);
            margin-bottom: 0.15rem;
        }

        .item-type-option .item-type-text small {
            display: block;
            font-size: 0.8rem;
            color: var(--text-secondary, #6b7280);
            line-height: 1.4;
        }

        .item-type-option .item-type-check {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary-color, #2563eb);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
            opacity: 0;
            transform: scale(0.6);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .item-type-option.active .item-type-check {
            opacity: 1;
            transform: scale(1);
        }

        /* Dark mode support */
        body.dark-mode .item-type-option {
            background: var(--dark-card, #1f2937);
            border-color: var(--dark-border, #374151);
        }

        body.dark-mode .item-type-option.active {
            background: color-mix(in srgb, var(--primary-color, #3b82f6) 12%, var(--dark-card, #1f2937));
            border-color: var(--primary-color, #3b82f6);
        }

        body.dark-mode .item-type-option .item-type-icon {
            background: var(--dark-bg-secondary, #111827);
        }

        body.dark-mode .item-type-option.active .item-type-icon {
            background: color-mix(in srgb, var(--primary-color, #3b82f6) 18%, #1f2937);
        }

        body.dark-mode .item-type-option .item-type-text strong {
            color: #f9fafb;
        }

        body.dark-mode .item-type-option .item-type-text small {
            color: #9ca3af;
        }

        /* Responsive */
        @media screen and (max-width: 640px) {
            .item-type-picker {
                flex-direction: column;
            }

            .item-type-option {
                min-width: unset;
                padding: 0.8rem 1rem;
            }

            .item-type-option .item-type-icon {
                font-size: 2rem;
                width: 48px;
                height: 48px;
            }
        }
    </style>

    {{-- ============================================================ --}}
    {{--  ISI KONTEN UTAMA                                            --}}
    {{-- ============================================================ --}}
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Ajukan Aset Baru</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <template id="assetTypeOptionsTemplate">
                <option value="">Pilih Jenis</option>
                @foreach ($assetTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </template>
            <form action="{{ route('requests.store') }}" method="POST" id="requestForm">
                @csrf

                <h4 style="margin-bottom: 1rem;">Informasi Umum</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_barang">Jenis Barang <span style="color: red;">*</span></label>
                        <select id="jenis_barang" name="jenis_barang"
                            class="form-control @error('jenis_barang') error @enderror" required>
                            @foreach ($jenisBarangOptions as $opt)
                                <option value="{{ $opt }}" {{ old('jenis_barang') === $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('jenis_barang')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="kategori_barang">Kategori Barang <span style="color: red;">*</span></label>
                        <select id="kategori_barang" name="kategori_barang"
                            class="form-control @error('kategori_barang') error @enderror" required>
                            @foreach ($kategoriBarangOptions as $opt)
                                <option value="{{ $opt }}" {{ old('kategori_barang') === $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('kategori_barang')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="priority">Prioritas <span style="color: red;">*</span></label>
                        <select id="priority" name="priority" class="form-control @error('priority') error @enderror"
                            required>
                            @foreach ($priorities as $p)
                                <option value="{{ $p }}"
                                    {{ old('priority', 'Normal') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                        @error('priority')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="alasan_pengajuan">Alasan Pengajuan <span style="color: red;">*</span></label>
                        <select id="alasan_pengajuan" name="alasan_pengajuan"
                            class="form-control @error('alasan_pengajuan') error @enderror" required
                            onchange="toggleRelatedAsset()">
                            @foreach ($alasanOptions as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('alasan_pengajuan') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('alasan_pengajuan')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" id="relatedAssetGroup"
                        style="display: {{ old('alasan_pengajuan') === 'Penggantian' ? 'block' : 'none' }};">
                        <label for="related_asset_id">Aset yang Diganti</label>
                        <select id="related_asset_id" name="related_asset_id"
                            class="form-control @error('related_asset_id') error @enderror">
                            <option value="">-- Pilih Aset --</option>
                            @foreach ($assets as $a)
                                <option value="{{ $a->id }}"
                                    {{ old('related_asset_id') == $a->id ? 'selected' : '' }}>{{ $a->asset_id }} —
                                    {{ $a->name }}</option>
                            @endforeach
                        </select>
                        @error('related_asset_id')
                            <div class="error-message" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Alasan / Latar Belakang Pengajuan <span style="color: red;">*</span></label>
                    <textarea id="reason" name="reason" class="form-control @error('reason') error @enderror" rows="3" required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="error-message" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Rincian Barang yang Diajukan</h4>

                <div id="itemsContainer">
                    @php $oldItems = old('items', [[]]); @endphp
                    @foreach ($oldItems as $index => $oldItem)
                        @php
                            $itemType = $oldItem['item_type'] ?? 'Fisik';
                            $isPhysical = $itemType === 'Fisik';
                        @endphp
                        <div class="item-row"
                            style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">

                            {{-- ============================================= --}}
                            {{--  PEMILIH JENIS ASET (KARTU AESTHETIC)         --}}
                            {{-- ============================================= --}}
                            <div class="item-type-picker">
                                <div class="item-type-option {{ $isPhysical ? 'active' : '' }}"
                                    data-index="{{ $index }}" data-value="Fisik"
                                    onclick="selectItemType({{ $index }}, 'Fisik')">
                                    <span class="item-type-icon">📦</span>
                                    <span class="item-type-text">
                                        <strong>Aset Fisik</strong>
                                        <small>Barang berwujud: laptop, meja, mesin, peralatan lab</small>
                                    </span>
                                    <span class="item-type-check">✓</span>
                                </div>
                                <div class="item-type-option {{ !$isPhysical ? 'active' : '' }}"
                                    data-index="{{ $index }}" data-value="Non-Fisik"
                                    onclick="selectItemType({{ $index }}, 'Non-Fisik')">
                                    <span class="item-type-icon">💾</span>
                                    <span class="item-type-text">
                                        <strong>Aset Non-Fisik</strong>
                                        <small>Lisensi software, akun digital, sertifikat/garansi</small>
                                    </span>
                                    <span class="item-type-check">✓</span>
                                </div>
                                {{-- hidden input untuk menyimpan nilai --}}
                                <input type="hidden" name="items[{{ $index }}][item_type]"
                                    value="{{ $itemType }}" id="item_type_hidden_{{ $index }}">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nama Barang <span style="color: red;">*</span></label>
                                    <input type="text" name="items[{{ $index }}][item_name]"
                                        class="form-control @error('items.' . $index . '.item_name') error @enderror"
                                        value="{{ $oldItem['item_name'] ?? '' }}" required>
                                    @error('items.' . $index . '.item_name')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group" id="assetTypeGroup_{{ $index }}"
                                    style="display: {{ $isPhysical ? 'block' : 'none' }};">
                                    <label>Jenis Aset <span style="color: red;">*</span></label>
                                    <select name="items[{{ $index }}][asset_type_id]"
                                        class="form-control @error('items.' . $index . '.asset_type_id') error @enderror"
                                        {{ $isPhysical ? 'required' : '' }}>
                                        <option value="">Pilih Jenis</option>
                                        @foreach ($assetTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ ($oldItem['asset_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.asset_type_id')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Spesifikasi</label>
                                    <input type="text" name="items[{{ $index }}][specification]"
                                        class="form-control" value="{{ $oldItem['specification'] ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label>Jumlah <span style="color: red;">*</span></label>
                                    <input type="number" name="items[{{ $index }}][quantity]"
                                        class="form-control @error('items.' . $index . '.quantity') error @enderror"
                                        value="{{ $oldItem['quantity'] ?? 1 }}" min="1" required>
                                    @error('items.' . $index . '.quantity')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Satuan <span style="color: red;">*</span></label>
                                    <input type="text" name="items[{{ $index }}][unit]"
                                        class="form-control @error('items.' . $index . '.unit') error @enderror"
                                        value="{{ $oldItem['unit'] ?? 'Pcs' }}" required>
                                    @error('items.' . $index . '.unit')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Est. Harga/Unit (Rp)</label>
                                    <input type="number" name="items[{{ $index }}][estimated_price_per_unit]"
                                        class="form-control" value="{{ $oldItem['estimated_price_per_unit'] ?? '' }}"
                                        min="0">
                                </div>
                            </div>
                            @if (count($oldItems) > 1)
                                <button type="button" class="btn btn-danger"
                                    onclick="this.closest('.item-row').remove()">Hapus Item Ini</button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary" onclick="addItemRow()" style="margin-bottom: 1.5rem;">+
                    Tambah Item Barang</button>

                <div class="btn-group">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemIndex = {{ count($oldItems) }};

        // ============================================================
        //  FUNGSI SELECT ITEM TYPE (PERBAIKAN FINAL)
        // ============================================================
        function selectItemType(index, type) {
            // 1. Cari hidden input dengan ID yang benar
            let hiddenInput = document.getElementById(`item_type_hidden_${index}`);
            if (!hiddenInput) {
                // Fallback: cari berdasarkan nama input
                hiddenInput = document.querySelector(`input[name="items[${index}][item_type]"]`);
            }
            if (!hiddenInput) {
                console.warn(`Hidden input for index ${index} not found`);
                return;
            }
            hiddenInput.value = type;

            // 2. Cari item-row terdekat dari hidden input
            const itemRow = hiddenInput.closest('.item-row');
            if (!itemRow) {
                console.warn(`Item row for index ${index} not found`);
                return;
            }

            // 3. Cari picker di dalam item-row tersebut
            const picker = itemRow.querySelector('.item-type-picker');
            if (!picker) {
                console.warn(`Picker for index ${index} not found`);
                return;
            }

            // 4. Update class aktif pada semua opsi di dalam picker ini
            const options = picker.querySelectorAll('.item-type-option');
            options.forEach(opt => {
                const isActive = opt.getAttribute('data-value') === type;
                opt.classList.toggle('active', isActive);
            });

            // 5. Tampilkan/sembunyikan dropdown Jenis Aset
            const group = document.getElementById(`assetTypeGroup_${index}`);
            if (!group) return;
            const isPhysical = type === 'Fisik';
            group.style.display = isPhysical ? 'block' : 'none';
            const select = group.querySelector('select');
            if (select) {
                select.required = isPhysical;
                if (!isPhysical) {
                    select.value = ''; // reset nilai jika non-fisik
                }
            }
        }

        // ============================================================
        //  FUNGSI TAMBAH ITEM BARANG (dengan ID hidden yang benar)
        // ============================================================
        function addItemRow() {
            const container = document.getElementById('itemsContainer');
            const assetTypeOptionsHtml = document.getElementById('assetTypeOptionsTemplate').innerHTML;
            const currentIndex = itemIndex;

            const row = document.createElement('div');
            row.className = 'item-row';
            row.style.cssText =
                'border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;';

            row.innerHTML = `
                <div class="item-type-picker">
                    <div class="item-type-option active" data-index="${currentIndex}" data-value="Fisik"
                        onclick="selectItemType(${currentIndex}, 'Fisik')">
                        <span class="item-type-icon">📦</span>
                        <span class="item-type-text">
                            <strong>Aset Fisik</strong>
                            <small>Barang berwujud: laptop, meja, mesin, peralatan lab</small>
                        </span>
                        <span class="item-type-check">✓</span>
                    </div>
                    <div class="item-type-option" data-index="${currentIndex}" data-value="Non-Fisik"
                        onclick="selectItemType(${currentIndex}, 'Non-Fisik')">
                        <span class="item-type-icon">💾</span>
                        <span class="item-type-text">
                            <strong>Aset Non-Fisik</strong>
                            <small>Lisensi software, akun digital, sertifikat/garansi</small>
                        </span>
                        <span class="item-type-check">✓</span>
                    </div>
                    <input type="hidden" name="items[${currentIndex}][item_type]" value="Fisik" id="item_type_hidden_${currentIndex}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Barang <span style="color:red;">*</span></label>
                        <input type="text" name="items[${currentIndex}][item_name]" class="form-control" required>
                    </div>
                    <div class="form-group" id="assetTypeGroup_${currentIndex}">
                        <label>Jenis Aset <span style="color:red;">*</span></label>
                        <select name="items[${currentIndex}][asset_type_id]" class="form-control" required>
                            ${assetTypeOptionsHtml}
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Spesifikasi</label>
                        <input type="text" name="items[${currentIndex}][specification]" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Jumlah <span style="color:red;">*</span></label>
                        <input type="number" name="items[${currentIndex}][quantity]" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Satuan <span style="color:red;">*</span></label>
                        <input type="text" name="items[${currentIndex}][unit]" class="form-control" value="Pcs" required>
                    </div>
                    <div class="form-group">
                        <label>Est. Harga/Unit (Rp)</label>
                        <input type="number" name="items[${currentIndex}][estimated_price_per_unit]" class="form-control" min="0">
                    </div>
                </div>
                <button type="button" class="btn btn-danger" onclick="this.closest('.item-row').remove()">Hapus Item Ini</button>
            `;
            container.appendChild(row);
            itemIndex++;
        }

        // ============================================================
        //  FUNGSI TOGGLE ASET TERKAIT
        // ============================================================
        function toggleRelatedAsset() {
            const alasan = document.getElementById('alasan_pengajuan').value;
            document.getElementById('relatedAssetGroup').style.display = alasan === 'Penggantian' ? 'block' : 'none';
        }

        // ============================================================
        //  INISIALISASI: pastikan semua opsi memiliki data-index & data-value
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.item-type-option').forEach(el => {
                if (!el.hasAttribute('data-index')) {
                    const picker = el.closest('.item-type-picker');
                    if (picker) {
                        const hidden = picker.querySelector('input[type="hidden"]');
                        if (hidden) {
                            const match = hidden.name.match(/items\[(\d+)\]/);
                            if (match) {
                                const idx = match[1];
                                el.setAttribute('data-index', idx);
                                // Set data-value berdasarkan apakah opsi ini aktif
                                if (el.classList.contains('active')) {
                                    const isFisik = el.querySelector('strong')?.textContent.includes(
                                        'Aset Fisik');
                                    el.setAttribute('data-value', isFisik ? 'Fisik' : 'Non-Fisik');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
