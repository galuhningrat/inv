@extends('layouts.app')

@section('title', 'Ajukan Aset')
@section('page-title', 'Ajukan Aset Baru')

@section('content')
    {{-- ============================================================ --}}
    {{-- CSS UNTUK KARTU PILIHAN YANG AESTHETIC --}}
    {{-- ============================================================ --}}
    <style>
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
    {{-- ISI KONTEN UTAMA --}}
    {{-- ============================================================ --}}
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Ajukan Aset Baru</h3>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        {{-- Notifikasi Rollover (jika ada) --}}
        @if (session('rollover_notification'))
            @php $notif = session('rollover_notification'); @endphp
            <div
                style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
                <p style="margin: 0; font-size: 0.9rem;">
                    <strong>🔄 Rollover Item Ditangguhkan</strong><br>
                    Terdapat <strong>{{ $notif['count'] }}</strong> aset dari pengajuan bulan lalu yang ditangguhkan oleh
                    Ketua STTI.
                    Aset tersebut telah otomatis dimasukkan ke dalam draft pengajuan bulan ini untuk Anda tinjau kembali.
                    <br><small style="color: #92400e;">Silakan periksa dan edit jika diperlukan sebelum mengajukan.</small>
                </p>
            </div>
        @endif

        {{-- Info periode --}}
        <div
            style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
            <p style="margin: 0; font-size: 0.9rem; color: #1e40af;">
                📅 Periode anggaran: <strong>{{ now()->translatedFormat('F Y') }}</strong>
            </p>
        </div>

        @php $oldItems = old('items', [[]]); @endphp
        <div style="padding: 2rem;">
            {{-- ============ TEMPLATE OPSI ============ --}}
            <template id="assetTypeOptionsTemplate">
                <option value="">Pilih Jenis</option>
                @foreach ($assetTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </template>
            <template id="nonFisikCategoryOptionsTemplate">
                <option value="">Pilih Kategori</option>
                @foreach ($nonFisikCategories as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </template>
            <template id="habisPakaiCategoryOptionsTemplate">
                <option value="">Pilih Kategori</option>
                @foreach (\App\Models\AssetRequestItem::HABIS_PAKAI_CATEGORIES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </template>
            <template id="unitOptionsTemplate">
                <option value="">Pilih Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->category ?? '-' }})</option>
                @endforeach
            </template>
            <template id="priorityOptionsTemplate">
                @foreach ($priorities as $p)
                    <option value="{{ $p }}" {{ $p === 'Normal' ? 'selected' : '' }}>{{ $p }}
                    </option>
                @endforeach
            </template>
            <template id="alasanOptionsTemplate">
                @foreach ($alasanOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
            </template>
            <template id="relatedAssetOptionsTemplate">
                <option value="">-- Pilih Aset --</option>
                @foreach ($assets as $a)
                    <option value="{{ $a->id }}">{{ $a->asset_id }} — {{ $a->name }}</option>
                @endforeach
            </template>

            {{-- Config untuk JS --}}
            <script>
                window.CAN_CHOOSE_UNIT = {{ $canChooseUnit ? 'true' : 'false' }};
                window.CURRENT_UNIT = {
                    id: {{ Auth::user()->unit_id ?? 'null' }},
                    name: @json($currentUnitName ?? '-')
                };
            </script>

            <form action="{{ route('requests.store') }}" method="POST" id="requestForm">
                @csrf

                <h4 style="margin-bottom: 1rem;">Rincian Barang yang Diajukan</h4>

                <div id="itemsContainer">
                    @foreach ($oldItems as $index => $oldItem)
                        @php
                            $itemType = $oldItem['item_type'] ?? 'Fisik';
                            $isPhysical = $itemType === 'Fisik';
                            $sifatOptions = $isPhysical
                                ? \App\Models\AssetRequestItem::SIFAT_BARANG_FISIK
                                : \App\Models\AssetRequestItem::SIFAT_BARANG_NON_FISIK;
                            $sifatBarang = $oldItem['sifat_barang'] ?? 'Tidak Habis Pakai';
                            if (!in_array($sifatBarang, $sifatOptions)) {
                                $sifatBarang = 'Tidak Habis Pakai';
                            }
                            $showAssetType = $isPhysical && $sifatBarang !== 'Habis Pakai';
                            $showHabisPakaiCategory = $isPhysical && $sifatBarang === 'Habis Pakai';
                            $showNonFisikCategory = !$isPhysical && $sifatBarang !== 'Jasa';
                        @endphp
                        <div class="item-row"
                            style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">

                            {{-- ===== PEMILIH JENIS ASET ===== --}}
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
                                <input type="hidden" name="items[{{ $index }}][item_type]"
                                    value="{{ $itemType }}" id="item_type_hidden_{{ $index }}">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Sifat Barang <span style="color: red;">*</span></label>
                                    <select id="sifat_barang_{{ $index }}"
                                        name="items[{{ $index }}][sifat_barang]"
                                        class="form-control @error('items.' . $index . '.sifat_barang') error @enderror"
                                        onchange="updateClassificationGroups({{ $index }})" required>
                                        @foreach ($sifatOptions as $opt)
                                            <option value="{{ $opt }}"
                                                {{ $sifatBarang === $opt ? 'selected' : '' }}>
                                                {{ $opt }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.sifat_barang')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                    style="display: {{ $showAssetType ? 'block' : 'none' }};">
                                    <label>Jenis Aset <span style="color: red;">*</span></label>
                                    <select name="items[{{ $index }}][asset_type_id]"
                                        class="form-control @error('items.' . $index . '.asset_type_id') error @enderror"
                                        {{ $showAssetType ? 'required' : '' }}>
                                        <option value="">Pilih Jenis</option>
                                        @foreach ($assetTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ ($oldItem['asset_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.asset_type_id')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group" id="habisPakaiCategoryGroup_{{ $index }}"
                                    style="display: {{ $showHabisPakaiCategory ? 'block' : 'none' }};">
                                    <label>Kategori Habis Pakai <span style="color: red;">*</span></label>
                                    <select name="items[{{ $index }}][category]"
                                        class="form-control @error('items.' . $index . '.category') error @enderror"
                                        {{ $showHabisPakaiCategory ? 'required' : '' }}>
                                        <option value="">Pilih Kategori</option>
                                        @foreach (\App\Models\AssetRequestItem::HABIS_PAKAI_CATEGORIES as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ ($oldItem['category'] ?? '') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.category')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group" id="nonFisikCategoryGroup_{{ $index }}"
                                    style="display: {{ $showNonFisikCategory ? 'block' : 'none' }};">
                                    <label>Kategori Non-Fisik <span style="color: red;">*</span></label>
                                    <select name="items[{{ $index }}][category]"
                                        class="form-control @error('items.' . $index . '.category') error @enderror"
                                        {{ $showNonFisikCategory ? 'required' : '' }}>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($nonFisikCategories as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ ($oldItem['category'] ?? '') === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.category')
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
                                    @php
                                        $unitOptions = $isPhysical ? $unitsFisik : $unitsNonFisik;
                                        $oldUnit = $oldItem['unit'] ?? ($isPhysical ? 'Pcs' : null);
                                        $isCustomUnit = $oldUnit && !in_array($oldUnit, $unitOptions);
                                    @endphp
                                    <label>Satuan <span style="color: red;">*</span></label>
                                    <select id="unit_select_{{ $index }}" name="items[{{ $index }}][unit]"
                                        class="form-control @error('items.' . $index . '.unit') error @enderror"
                                        onchange="handleUnitChange({{ $index }})"
                                        {{ $isCustomUnit ? 'disabled' : 'required' }}>
                                        <option value="">-- Pilih Satuan --</option>
                                        @foreach ($unitOptions as $opt)
                                            <option value="{{ $opt }}"
                                                {{ $oldUnit === $opt ? 'selected' : '' }}>
                                                {{ $opt }}
                                            </option>
                                        @endforeach
                                        <option value="Lainnya" {{ $isCustomUnit ? 'selected' : '' }}>Lainnya (isi
                                            manual)</option>
                                    </select>
                                    <input type="text" id="unit_manual_{{ $index }}"
                                        name="items[{{ $index }}][unit]" class="form-control"
                                        style="display: {{ $isCustomUnit ? 'block' : 'none' }}; margin-top: 0.5rem;"
                                        placeholder="Tulis satuan, contoh: Rim, Dus, Botol"
                                        value="{{ $isCustomUnit ? $oldUnit : '' }}"
                                        oninput="updatePriceLabel({{ $index }})"
                                        {{ $isCustomUnit ? 'required' : 'disabled' }}>
                                    @error('items.' . $index . '.unit')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    {{-- Label dinamis: JS akan mengubah teksnya mengikuti satuan yang dipilih --}}
                                    <label id="price_label_{{ $index }}">Est. Harga Satuan (Rp)</label>
                                    <input type="number" name="items[{{ $index }}][estimated_price_per_unit]"
                                        class="form-control" value="{{ $oldItem['estimated_price_per_unit'] ?? '' }}"
                                        min="0">
                                </div>
                            </div>

                            {{-- ============ FASE 2: FIELD PER-ITEM ============ --}}
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                                <p
                                    style="font-weight: 600; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                    📌 Detail Pengajuan Item Ini
                                </p>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Unit Pengaju <span style="color: red;">*</span></label>
                                        @if ($canChooseUnit)
                                            <select name="items[{{ $index }}][unit_id]"
                                                class="form-control @error('items.' . $index . '.unit_id') error @enderror"
                                                required>
                                                <option value="">Pilih Unit</option>
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}"
                                                        {{ ($oldItem['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->category ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control"
                                                value="{{ $currentUnitName ?? '-' }}" disabled>
                                            <input type="hidden" name="items[{{ $index }}][unit_id]"
                                                value="{{ Auth::user()->unit_id }}">
                                        @endif
                                        @error('items.' . $index . '.unit_id')
                                            <div class="error-message" style="display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Prioritas <span style="color: red;">*</span></label>
                                        <select name="items[{{ $index }}][priority]" class="form-control"
                                            required>
                                            @foreach ($priorities as $p)
                                                <option value="{{ $p }}"
                                                    {{ ($oldItem['priority'] ?? 'Normal') === $p ? 'selected' : '' }}>
                                                    {{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="section-divider">
                                    <p class="section-title">📌 Detail Pengajuan Item Ini</p>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Unit Pengaju <span style="color: red;">*</span></label>
                                            {{-- ... --}}
                                        </div>
                                        <div class="form-group">
                                            <label>Prioritas <span style="color: red;">*</span></label>
                                            {{-- ... --}}
                                        </div>
                                    </div>

                                    {{-- ← blok "Alasan Pengajuan" yang lama DIHAPUS di sini --}}

                                    <div class="form-group">
                                        <label>Alasan / Latar Belakang Item Ini <span style="color: red;">*</span></label>
                                        <textarea name="items[{{ $index }}][reason]" class="form-control" rows="2"
                                            placeholder="Jelaskan mengapa jasa ini dibutuhkan..." required>{{ $oldItem['reason'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Alasan / Latar Belakang Item Ini <span style="color: red;">*</span></label>
                                    <textarea name="items[{{ $index }}][reason]"
                                        class="form-control @error('items.' . $index . '.reason') error @enderror" rows="2"
                                        placeholder="Jelaskan mengapa item ini diajukan..." required>{{ $oldItem['reason'] ?? '' }}</textarea>
                                    @error('items.' . $index . '.reason')
                                        <div class="error-message" style="display: block;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if (count($oldItems) > 1)
                                <button type="button" class="btn btn-danger" style="margin-top: 1rem;"
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
        //  FUNGSI SELECT ITEM TYPE
        // ============================================================
        function selectItemType(index, type) {
            let hiddenInput = document.getElementById(`item_type_hidden_${index}`);
            if (!hiddenInput) {
                hiddenInput = document.querySelector(`input[name="items[${index}][item_type]"]`);
            }
            if (!hiddenInput) {
                console.warn(`Hidden input for index ${index} not found`);
                return;
            }
            hiddenInput.value = type;

            const itemRow = hiddenInput.closest('.item-row');
            if (!itemRow) {
                console.warn(`Item row for index ${index} not found`);
                return;
            }

            const picker = itemRow.querySelector('.item-type-picker');
            if (!picker) {
                console.warn(`Picker for index ${index} not found`);
                return;
            }

            const options = picker.querySelectorAll('.item-type-option');
            options.forEach(opt => {
                const isActive = opt.getAttribute('data-value') === type;
                opt.classList.toggle('active', isActive);
            });

            populateSifatBarangOptions(index, type);
            updateClassificationGroups(index);
            populateUnitOptions(index, type);
        }

        // ============================================================
        //  FUNGSI SIFAT BARANG + GRUP KLASIFIKASI
        // ============================================================
        const SIFAT_BARANG_OPTIONS = {
            'Fisik': {{ \Illuminate\Support\Js::from($sifatBarangFisik) }},
            'Non-Fisik': {{ \Illuminate\Support\Js::from($sifatBarangNonFisik) }},
        };

        function populateSifatBarangOptions(index, type) {
            const select = document.getElementById(`sifat_barang_${index}`);
            if (!select) return;
            const options = SIFAT_BARANG_OPTIONS[type] || SIFAT_BARANG_OPTIONS['Fisik'];
            select.innerHTML = '';
            options.forEach(opt => {
                const el = document.createElement('option');
                el.value = opt;
                el.textContent = opt;
                select.appendChild(el);
            });
            select.value = 'Tidak Habis Pakai';
        }

        function updateClassificationGroups(index) {
            const itemType = document.getElementById(`item_type_hidden_${index}`)?.value || 'Fisik';
            const sifatSelect = document.getElementById(`sifat_barang_${index}`);
            const sifat = sifatSelect ? sifatSelect.value : 'Tidak Habis Pakai';

            const groups = {
                assetType: document.getElementById(`assetTypeGroup_${index}`),
                habisPakai: document.getElementById(`habisPakaiCategoryGroup_${index}`),
                nonFisik: document.getElementById(`nonFisikCategoryGroup_${index}`),
            };

            Object.values(groups).forEach(g => {
                if (!g) return;
                g.style.display = 'none';
                const select = g.querySelector('select');
                if (select) {
                    select.required = false;
                    select.value = '';
                }
            });

            let active = null;
            if (itemType === 'Fisik' && sifat !== 'Habis Pakai') {
                active = groups.assetType;
            } else if (itemType === 'Fisik' && sifat === 'Habis Pakai') {
                active = groups.habisPakai;
            } else if (itemType === 'Non-Fisik' && sifat !== 'Jasa') {
                active = groups.nonFisik;
            }

            if (active) {
                active.style.display = 'block';
                const select = active.querySelector('select');
                if (select) select.required = true;
            }
        }

        // ============================================================
        //  FUNGSI SATUAN
        // ============================================================
        const UNIT_OPTIONS = {
            'Fisik': {{ \Illuminate\Support\Js::from($unitsFisik) }},
            'Non-Fisik': {{ \Illuminate\Support\Js::from($unitsNonFisik) }},
        };

        function populateUnitOptions(index, type, selectedValue = null) {
            const select = document.getElementById(`unit_select_${index}`);
            if (!select) return;
            const options = UNIT_OPTIONS[type] || UNIT_OPTIONS['Fisik'];

            select.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Pilih Satuan --';
            select.appendChild(placeholder);
            options.forEach(opt => {
                const el = document.createElement('option');
                el.value = opt;
                el.textContent = opt;
                if (selectedValue === opt) el.selected = true;
                select.appendChild(el);
            });
            const lainnya = document.createElement('option');
            lainnya.value = 'Lainnya';
            lainnya.textContent = 'Lainnya (isi manual)';
            select.appendChild(lainnya);

            const manualInput = document.getElementById(`unit_manual_${index}`);
            if (manualInput) {
                manualInput.style.display = 'none';
                manualInput.disabled = true;
                manualInput.value = '';
            }
            select.disabled = false;

            // Reset label harga ke default karena dropdown baru saja di-reset
            updatePriceLabel(index);
        }

        function handleUnitChange(index) {
            const select = document.getElementById(`unit_select_${index}`);
            const manualInput = document.getElementById(`unit_manual_${index}`);
            if (!select || !manualInput) return;

            if (select.value === 'Lainnya') {
                manualInput.style.display = 'block';
                manualInput.disabled = false;
                select.disabled = true;
                manualInput.focus();
            } else {
                manualInput.style.display = 'none';
                manualInput.disabled = true;
                manualInput.value = '';
                select.disabled = false;
            }

            updatePriceLabel(index);
        }

        // ============================================================
        //  FUNGSI LABEL HARGA DINAMIS
        //  Menyesuaikan teks label "Est. Harga/xxx (Rp)" mengikuti
        //  satuan yang dipilih (dropdown atau input manual).
        // ============================================================
        function updatePriceLabel(index) {
            const label = document.getElementById(`price_label_${index}`);
            if (!label) return;

            const select = document.getElementById(`unit_select_${index}`);
            const manualInput = document.getElementById(`unit_manual_${index}`);

            let unitText = '';

            // Prioritas 1: input manual aktif & ada isinya
            if (manualInput && !manualInput.disabled && manualInput.value.trim() !== '') {
                unitText = manualInput.value.trim();
            }
            // Prioritas 2: dropdown dipilih & bukan "Lainnya"
            else if (select && !select.disabled && select.value && select.value !== 'Lainnya') {
                unitText = select.value;
            }
            // Fallback: label default

            label.textContent = unitText ?
                `Est. Harga/${unitText} (Rp)` :
                'Est. Harga Satuan (Rp)';
        }

        // ============================================================
        //  FASE 2: TOGGLE ASET TERKAIT PER ITEM
        // ============================================================
        function toggleRelatedAssetForItem(index, selectEl) {
            const group = document.getElementById(`relatedAssetGroup_${index}`);
            if (!group) return;
            const isPenggantian = selectEl.value === 'Penggantian';
            group.style.display = isPenggantian ? 'block' : 'none';
            const relatedSelect = group.querySelector('select');
            if (relatedSelect) {
                relatedSelect.required = isPenggantian;
                if (!isPenggantian) relatedSelect.value = '';
            }
        }

        // ============================================================
        //  FUNGSI TAMBAH ITEM BARANG (FASE 2)
        // ============================================================
        function addItemRow() {
            const container = document.getElementById('itemsContainer');
            const assetTypeOptionsHtml = document.getElementById('assetTypeOptionsTemplate').innerHTML;
            const nonFisikCategoryOptionsHtml = document.getElementById('nonFisikCategoryOptionsTemplate').innerHTML;
            const habisPakaiCategoryOptionsHtml = document.getElementById('habisPakaiCategoryOptionsTemplate').innerHTML;
            const unitOptionsHtml = document.getElementById('unitOptionsTemplate').innerHTML;
            const priorityOptionsHtml = document.getElementById('priorityOptionsTemplate').innerHTML;
            const alasanOptionsHtml = document.getElementById('alasanOptionsTemplate').innerHTML;
            const relatedAssetOptionsHtml = document.getElementById('relatedAssetOptionsTemplate').innerHTML;
            const currentIndex = itemIndex;

            const unitField = window.CAN_CHOOSE_UNIT ?
                `<select name="items[${currentIndex}][unit_id]" class="form-control" required>${unitOptionsHtml}</select>` :
                `<input type="text" class="form-control" value="${window.CURRENT_UNIT.name}" disabled>
                       <input type="hidden" name="items[${currentIndex}][unit_id]" value="${window.CURRENT_UNIT.id}">`;

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
                            <label>Sifat Barang <span style="color:red;">*</span></label>
                            <select id="sifat_barang_${currentIndex}" name="items[${currentIndex}][sifat_barang]" class="form-control" onchange="updateClassificationGroups(${currentIndex})" required></select>
                        </div>
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
                        <div class="form-group" id="habisPakaiCategoryGroup_${currentIndex}" style="display:none;">
                            <label>Kategori Habis Pakai <span style="color:red;">*</span></label>
                            <select name="items[${currentIndex}][category]" class="form-control">
                                ${habisPakaiCategoryOptionsHtml}
                            </select>
                        </div>
                        <div class="form-group" id="nonFisikCategoryGroup_${currentIndex}" style="display:none;">
                            <label>Kategori Non-Fisik <span style="color:red;">*</span></label>
                            <select name="items[${currentIndex}][category]" class="form-control">
                                ${nonFisikCategoryOptionsHtml}
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
                            <select id="unit_select_${currentIndex}" name="items[${currentIndex}][unit]" class="form-control" onchange="handleUnitChange(${currentIndex})" required></select>
                            <input type="text" id="unit_manual_${currentIndex}" name="items[${currentIndex}][unit]" class="form-control" style="display:none; margin-top:0.5rem;" placeholder="Tulis satuan, contoh: Rim, Dus, Botol" oninput="updatePriceLabel(${currentIndex})" disabled>
                        </div>
                        <div class="form-group">
                            <label id="price_label_${currentIndex}">Est. Harga Satuan (Rp)</label>
                            <input type="number" name="items[${currentIndex}][estimated_price_per_unit]" class="form-control" min="0">
                        </div>
                    </div>

                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-color);">
                        <p style="font-weight: 600; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                            📌 Detail Pengajuan Item Ini
                        </p>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Unit Pengaju <span style="color:red;">*</span></label>
                                ${unitField}
                            </div>
                            <div class="form-group">
                                <label>Prioritas <span style="color:red;">*</span></label>
                                <select name="items[${currentIndex}][priority]" class="form-control" required>
                                    ${priorityOptionsHtml}
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Alasan Pengajuan <span style="color:red;">*</span></label>
                                <select name="items[${currentIndex}][alasan_pengajuan]" class="form-control" required
                                    onchange="toggleRelatedAssetForItem(${currentIndex}, this)">
                                    ${alasanOptionsHtml}
                                </select>
                            </div>
                            <div class="form-group" id="relatedAssetGroup_${currentIndex}" style="display: none;">
                                <label>Aset yang Diganti</label>
                                <select name="items[${currentIndex}][related_asset_id]" class="form-control">
                                    ${relatedAssetOptionsHtml}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alasan / Latar Belakang Item Ini <span style="color:red;">*</span></label>
                            <textarea name="items[${currentIndex}][reason]" class="form-control" rows="2" placeholder="Jelaskan mengapa item ini diajukan..." required></textarea>
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger" style="margin-top: 1rem;" onclick="this.closest('.item-row').remove()">Hapus Item Ini</button>
                `;
            container.appendChild(row);
            populateSifatBarangOptions(currentIndex, 'Fisik');
            updateClassificationGroups(currentIndex);
            populateUnitOptions(currentIndex, 'Fisik');
            itemIndex++;
        }

        // ============================================================
        //  INISIALISASI
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan semua opsi picker punya data-index & data-value
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

            // Inisialisasi label harga untuk semua baris yang sudah ada di DOM.
            // Penting untuk kasus form di-submit → validasi gagal → old() reload,
            // atau baris lama dengan satuan custom yang sudah diisi.
            document.querySelectorAll('.item-row').forEach((row) => {
                const hidden = row.querySelector('input[name$="[item_type]"]');
                if (!hidden) return;
                const match = hidden.name.match(/items\[(\d+)\]/);
                if (match) {
                    updatePriceLabel(match[1]);
                }
            });
        });
    </script>
@endpush
