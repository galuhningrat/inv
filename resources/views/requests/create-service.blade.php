@extends('layouts.app')

@section('title', 'Pengajuan Jasa')
@section('page-title', 'Pengajuan Jasa: ' . $categoryLabel)

@section('content')
    <style>
        .service-badge {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .item-row {
            border: 1px solid var(--border-color);
            padding: 1.25rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            background: var(--card-background);
        }

        .section-title {
            font-weight: 600;
            margin: 1rem 0 0.75rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-divider {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--border-color);
        }

        .service-fields-wrapper {
            background: color-mix(in srgb, var(--primary-color, #2563eb) 4%, transparent);
            padding: 1rem;
            border-radius: 8px;
            margin: 0.75rem 0;
            border-left: 3px solid var(--primary-color, #2563eb);
        }
    </style>

    <div class="data-table-container">
        <div class="table-header">
            <div>
                <span class="service-badge">🛠️ Jasa</span>
                <h3 class="table-title" style="margin: 0;">{{ $categoryLabel }}</h3>
            </div>
            <a href="{{ route('requests.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        <div
            style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 1rem 1.5rem; margin: 1rem 2rem;">
            <p style="margin: 0; font-size: 0.9rem; color: #1e40af;">
                📅 Periode anggaran: <strong>{{ now()->translatedFormat('F Y') }}</strong>
                &nbsp;·&nbsp;
                🏷️ Kategori: <strong>{{ $categoryLabel }}</strong>
            </p>
        </div>

        @php $oldItems = old('items', [[]]); @endphp

        <div style="padding: 1rem 2rem 2rem;">
            {{-- ============ TEMPLATE: field spesifik kategori ============ --}}
            <template id="serviceFieldsTemplate">
                @include('requests.partials.service-' . $category, ['index' => '__INDEX__'])
            </template>

            {{-- ============ TEMPLATE: opsi dropdown shared ============ --}}
            <template id="unitOptionsTemplate">
                <option value="">Pilih Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->category ?? '-' }})</option>
                @endforeach
            </template>
            <template id="priorityOptionsTemplate">
                @foreach ($priorities as $p)
                    <option value="{{ $p }}" {{ $p === 'Normal' ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </template>

            <script>
                window.CAN_CHOOSE_UNIT = {{ $canChooseUnit ? 'true' : 'false' }};
                window.CURRENT_UNIT = {
                    id: {{ Auth::user()->unit_id ?? 'null' }},
                    name: @json($currentUnitName ?? '-')
                };
            </script>

            <form action="{{ route('requests.store-service', $category) }}" method="POST" id="serviceForm">
                @csrf

                @if ($errors->any())
                    <div
                        style="background: #fee2e2; border: 1px solid #f87171; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <strong style="color: #991b1b;">Mohon periksa kembali form Anda:</strong>
                        <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem; color: #991b1b;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h4 style="margin-bottom: 1rem;">Rincian Jasa yang Diajukan</h4>

                <div id="itemsContainer">
                    @foreach ($oldItems as $index => $oldItem)
                        <div class="item-row">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <strong style="font-size: 0.9rem;">Item #{{ $index + 1 }}</strong>
                                @if (count($oldItems) > 1)
                                    <button type="button" class="btn btn-danger"
                                        style="padding: 0.25rem 0.75rem; font-size: 0.8rem;"
                                        onclick="this.closest('.item-row').remove()">Hapus</button>
                                @endif
                            </div>

                            {{-- Field umum --}}
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nama Jasa <span style="color: red;">*</span></label>
                                    <input type="text" name="items[{{ $index }}][item_name]" class="form-control"
                                        value="{{ $oldItem['item_name'] ?? '' }}"
                                        placeholder="Contoh: Servis AC Lab Komputer" required>
                                </div>
                                <div class="form-group">
                                    <label>Spesifikasi</label>
                                    <input type="text" name="items[{{ $index }}][specification]"
                                        class="form-control" value="{{ $oldItem['specification'] ?? '' }}"
                                        placeholder="Detail tambahan (opsional)">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jumlah <span style="color: red;">*</span></label>
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control"
                                        value="{{ $oldItem['quantity'] ?? 1 }}" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label>Satuan <span style="color: red;">*</span></label>
                                    <input type="text" name="items[{{ $index }}][unit]" class="form-control"
                                        value="{{ $oldItem['unit'] ?? 'Paket' }}" placeholder="Paket / Unit / Kegiatan"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label>Est. Harga Satuan (Rp)</label>
                                    <input type="number" name="items[{{ $index }}][estimated_price_per_unit]"
                                        class="form-control" value="{{ $oldItem['estimated_price_per_unit'] ?? '' }}"
                                        min="0">
                                </div>
                            </div>

                            {{-- Field spesifik kategori --}}
                            <div class="service-fields-wrapper">
                                <p class="section-title" style="margin-top: 0;">📋 Detail {{ $categoryLabel }}</p>
                                @include('requests.partials.service-' . $category, ['index' => $index])
                            </div>

                            {{-- Field per-item --}}
                            <div class="section-divider">
                                <p class="section-title">📌 Detail Pengajuan Item Ini</p>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Unit Pengaju <span style="color: red;">*</span></label>
                                        @if ($canChooseUnit)
                                            <select name="items[{{ $index }}][unit_id]" class="form-control"
                                                required>
                                                <option value="">Pilih Unit</option>
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}"
                                                        {{ ($oldItem['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->category ?? '-' }})</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control"
                                                value="{{ $currentUnitName ?? '-' }}" disabled>
                                            <input type="hidden" name="items[{{ $index }}][unit_id]"
                                                value="{{ Auth::user()->unit_id }}">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Prioritas <span style="color: red;">*</span></label>
                                        <select name="items[{{ $index }}][priority]" class="form-control" required>
                                            @foreach ($priorities as $p)
                                                <option value="{{ $p }}"
                                                    {{ ($oldItem['priority'] ?? 'Normal') === $p ? 'selected' : '' }}>
                                                    {{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Alasan / Latar Belakang Item Ini <span style="color: red;">*</span></label>
                                    <textarea name="items[{{ $index }}][reason]" class="form-control" rows="2"
                                        placeholder="Jelaskan mengapa jasa ini dibutuhkan..." required>{{ $oldItem['reason'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary" onclick="addItemRow()" style="margin-bottom: 1.5rem;">+
                    Tambah Item Jasa</button>

                <div class="btn-group">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Ajukan Jasa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemIndex = {{ count($oldItems) }};

        // Template field spesifik kategori dari <template>
        const SERVICE_FIELDS_TEMPLATE = document.getElementById('serviceFieldsTemplate').innerHTML;
        const UNIT_OPTIONS_HTML = document.getElementById('unitOptionsTemplate').innerHTML;
        const PRIORITY_OPTIONS_HTML = document.getElementById('priorityOptionsTemplate').innerHTML;

        function buildServiceFieldsHtml(index) {
            // Replace semua __INDEX__ di template dengan indeks sebenarnya
            return SERVICE_FIELDS_TEMPLATE.replace(/__INDEX__/g, index);
        }

        function addItemRow() {
            const container = document.getElementById('itemsContainer');
            const idx = itemIndex;

            const unitField = window.CAN_CHOOSE_UNIT ?
                `<select name="items[${idx}][unit_id]" class="form-control" required>${UNIT_OPTIONS_HTML}</select>` :
                `<input type="text" class="form-control" value="${window.CURRENT_UNIT.name}" disabled>
                   <input type="hidden" name="items[${idx}][unit_id]" value="${window.CURRENT_UNIT.id}">`;

            const row = document.createElement('div');
            row.className = 'item-row';

            row.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <strong style="font-size: 0.9rem;">Item #${idx + 1}</strong>
                    <button type="button" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;"
                        onclick="this.closest('.item-row').remove()">Hapus</button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Jasa <span style="color:red;">*</span></label>
                        <input type="text" name="items[${idx}][item_name]" class="form-control" placeholder="Contoh: Servis AC Lab Komputer" required>
                    </div>
                    <div class="form-group">
                        <label>Spesifikasi</label>
                        <input type="text" name="items[${idx}][specification]" class="form-control" placeholder="Detail tambahan (opsional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jumlah <span style="color:red;">*</span></label>
                        <input type="number" name="items[${idx}][quantity]" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Satuan <span style="color:red;">*</span></label>
                        <input type="text" name="items[${idx}][unit]" class="form-control" value="Paket" placeholder="Paket / Unit / Kegiatan" required>
                    </div>
                    <div class="form-group">
                        <label>Est. Harga Satuan (Rp)</label>
                        <input type="number" name="items[${idx}][estimated_price_per_unit]" class="form-control" min="0">
                    </div>
                </div>

                <div class="service-fields-wrapper">
                    <p class="section-title" style="margin-top: 0;">📋 Detail ${@json($categoryLabel)}</p>
                    ${buildServiceFieldsHtml(idx)}
                </div>

                <div class="section-divider">
                    <p class="section-title">📌 Detail Pengajuan Item Ini</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Unit Pengaju <span style="color:red;">*</span></label>
                            ${unitField}
                        </div>
                        <div class="form-group">
                            <label>Prioritas <span style="color:red;">*</span></label>
                            <select name="items[${idx}][priority]" class="form-control" required>
                                ${PRIORITY_OPTIONS_HTML}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alasan / Latar Belakang Item Ini <span style="color:red;">*</span></label>
                        <textarea name="items[${idx}][reason]" class="form-control" rows="2" placeholder="Jelaskan mengapa jasa ini dibutuhkan..." required></textarea>
                    </div>
                </div>
            `;

            container.appendChild(row);
            itemIndex++;
        }
    </script>
@endpush
