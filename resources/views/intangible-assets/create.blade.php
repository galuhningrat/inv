@extends('layouts.app')

@section('title', 'Tambah Aset Non-Fisik')
@section('page-title', 'Tambah Aset Non-Fisik')

@section('content')
    {{-- ============================================================ --}}
    {{--  PEMILIH JENIS ASET – HERO CARD                              --}}
    {{-- ============================================================ --}}
    <div class="asset-type-selector">
        <div class="asset-type-selector__inner">
            <h2 class="asset-type-selector__title">📋 Pilih Jenis Aset</h2>
            <p class="asset-type-selector__subtitle">
                Tentukan kategori aset sebelum mengisi formulir di bawah.
            </p>

            <div class="asset-type-cards">
                {{-- Card Aset Fisik --}}
                <a href="{{ route('assets-inv.create') }}"
                    class="asset-type-card {{ request()->routeIs('assets-inv.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">📦</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Fisik</h5>
                        <p>Barang berwujud: komputer, furnitur, peralatan lab, dan sejenisnya</p>
                    </div>
                    @if (request()->routeIs('assets-inv.create'))
                        <span class="asset-type-card__check">✓</span>
                    @endif
                </a>

                {{-- Card Aset Non-Fisik (aktif) --}}
                <a href="{{ route('intangible-assets.create') }}"
                    class="asset-type-card {{ request()->routeIs('intangible-assets.create') ? 'active' : '' }}">
                    <div class="asset-type-card__icon">💾</div>
                    <div class="asset-type-card__body">
                        <h5>Aset Non-Fisik <span class="badge-prototype">Prototipe</span></h5>
                        <p>Lisensi software, HAKI, jurnal ilmiah, domain/hosting</p>
                    </div>
                    @if (request()->routeIs('intangible-assets.create'))
                        <span class="asset-type-card__check">✓</span>
                    @endif
                </a>
            </div>

            <div class="asset-type-selector__hint">
                💡 <strong>Tips:</strong> Pilih jenis aset yang sesuai. Halaman ini khusus untuk <strong>Aset
                    Non-Fisik</strong> (Prototipe).
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--  FORM TAMBAH ASET NON-FISIK                                  --}}
    {{-- ============================================================ --}}
    <div class="data-table-container" style="margin-top: 2rem;">
        <div class="table-header">
            <h3 class="table-title">Formulir Aset Non-Fisik</h3>
            <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>

        <div style="padding: 2rem;">
            {{-- Peringatan prototipe --}}
            <div
                style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                ⚠️ <strong>Mode Prototipe:</strong> Modul ini belum tercantum di SRS_SINADAS.docx dan belum melalui proses
                RBAC formal. Hanya dapat diakses Sarpras/Admin sementara.
            </div>

            <form action="{{ route('intangible-assets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h4 style="margin-bottom: 1rem;">Informasi Utama</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Aset Non-Fisik <span style="color:red;">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') error @enderror" value="{{ old('name') }}"
                            placeholder="mis. Lisensi MATLAB Kampus" required>
                        @error('name')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori <span style="color:red;">*</span></label>
                        <select id="category" name="category" class="form-control @error('category') error @enderror"
                            required>
                            <option value="">Pilih Kategori</option>
                            @foreach (['Software' => 'Perangkat Lunak / Software', 'HAKI/Paten' => 'Hak Kekayaan Intelektual (HAKI/Paten/Hak Cipta)', 'Jurnal Ilmiah' => 'Langganan Jurnal / Database Akademik', 'Domain/Hosting' => 'Domain & Layanan Cloud/Hosting', 'Kurikulum' => 'Lisensi Kurikulum / Hak Waralaba Pendidikan'] as $val => $label)
                                <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label for="vendor">Vendor / Penyedia / Penerbit <span style="color:red;">*</span></label>
                    <input type="text" id="vendor" name="vendor"
                        class="form-control @error('vendor') error @enderror" value="{{ old('vendor') }}"
                        placeholder="mis. MathWorks Inc" required>
                    @error('vendor')
                        <div class="error-message" style="display:block;">{{ $message }}</div>
                    @enderror
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Aspek Finansial &amp; Legalitas</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Harga Pembelian/Nilai Perolehan (Rp) <span style="color:red;">*</span></label>
                        <input type="number" id="price" name="price"
                            class="form-control @error('price') error @enderror" value="{{ old('price') }}" min="0"
                            required>
                        @error('price')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="activation_date">Tanggal Aktivasi/Pembelian <span style="color:red;">*</span></label>
                        <input type="date" id="activation_date" name="activation_date"
                            class="form-control @error('activation_date') error @enderror"
                            value="{{ old('activation_date', date('Y-m-d')) }}" required>
                        @error('activation_date')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="funding_source">Sumber Pendanaan</label>
                        <select id="funding_source" name="funding_source" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($fundingSources as $fs)
                                <option value="{{ $fs }}" {{ old('funding_source') === $fs ? 'selected' : '' }}>
                                    {{ $fs }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contract_number">Nomor Kontrak / Sertifikat / SK</label>
                        <input type="text" id="contract_number" name="contract_number" class="form-control"
                            value="{{ old('contract_number') }}">
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Masa Berlaku &amp; Kuota</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Lisensi <span style="color:red;">*</span></label>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.5rem;">
                            <label style="font-weight: normal;"><input type="radio" name="license_type"
                                    value="Berlangganan" onchange="toggleExpiry()"
                                    {{ old('license_type') === 'Berlangganan' ? 'checked' : '' }} required>
                                Berlangganan</label>
                            <label style="font-weight: normal;"><input type="radio" name="license_type"
                                    value="Selamanya" onchange="toggleExpiry()"
                                    {{ old('license_type') === 'Selamanya' ? 'checked' : '' }}> Selamanya
                                (Perpetual)</label>
                        </div>
                        @error('license_type')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="quota">Kapasitas / Kuota Pengguna</label>
                        <input type="text" id="quota" name="quota" class="form-control"
                            value="{{ old('quota') }}" placeholder='mis. "100 Users", "Unlimited"'>
                    </div>
                </div>
                <div class="form-row" id="expiryGroup"
                    style="display: {{ old('license_type') === 'Berlangganan' ? 'flex' : 'none' }};">
                    <div class="form-group">
                        <label for="expiry_date">Tanggal Kedaluwarsa <span style="color:red;">*</span></label>
                        <input type="date" id="expiry_date" name="expiry_date"
                            class="form-control @error('expiry_date') error @enderror" value="{{ old('expiry_date') }}">
                        @error('expiry_date')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="reminder_days">Ingatkan Sebelum Habis</label>
                        <select id="reminder_days" name="reminder_days" class="form-control">
                            <option value="">Tidak ada pengingat</option>
                            <option value="30" {{ old('reminder_days') == '30' ? 'selected' : '' }}>30 hari</option>
                            <option value="14" {{ old('reminder_days') == '14' ? 'selected' : '' }}>14 hari</option>
                            <option value="7" {{ old('reminder_days') == '7' ? 'selected' : '' }}>7 hari</option>
                        </select>
                        <small class="form-text">⚠️ Prototipe: kolom tersimpan, tapi notifikasi otomatis belum aktif (butuh
                            job scheduler terpisah).</small>
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">
                <h4 style="margin-bottom: 1rem;">Penempatan Digital</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_id">Unit Pengelola / Penanggung Jawab <span style="color:red;">*</span></label>
                        <select id="unit_id" name="unit_id" class="form-control @error('unit_id') error @enderror"
                            required>
                            <option value="">Pilih Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->category }}: {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="error-message" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="pic_id">Penanggung Jawab Aset / PIC</label>
                        <select id="pic_id" name="pic_id" class="form-control">
                            <option value="">-- Tidak ditentukan --</option>
                            @foreach ($usersByLevel as $level => $usersInLevel)
                                <optgroup label="{{ $level }}">
                                    @foreach ($usersInLevel as $u)
                                        <option value="{{ $u->id }}"
                                            {{ old('pic_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="access_url">URL Akses / Dasbor Admin</label>
                        <input type="url" id="access_url" name="access_url" class="form-control"
                            value="{{ old('access_url') }}" placeholder="https://...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="certificate_file">File Sertifikat / Bukti Kepemilikan</label>
                    <input type="file" id="certificate_file" name="certificate_file" class="form-control"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <small class="form-text">PDF/JPG/PNG, maks 5MB</small>
                    @error('certificate_file')
                        <div class="error-message" style="display:block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-group">
                    <a href="{{ route('intangible-assets.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan (Prototipe)</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* =============================================
                   ASSET TYPE SELECTOR (Hero Card) – SAMA SEPERTI DI ATAS
                ============================================= */
        .asset-type-selector {
            background: var(--card-background);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 2rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .asset-type-selector__inner {
            max-width: 900px;
            margin: 0 auto;
        }

        .asset-type-selector__title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
            color: var(--text-primary);
        }

        .asset-type-selector__subtitle {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin: 0 0 1.75rem;
        }

        .asset-type-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .asset-type-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.5rem 1.75rem;
            border: 2px solid var(--border-color);
            border-radius: 14px;
            text-decoration: none;
            color: var(--text-primary);
            background: var(--light-bg);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .asset-type-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }

        .asset-type-card.active {
            border-color: var(--primary-color);
            background: color-mix(in srgb, var(--primary-color) 8%, var(--card-background));
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .asset-type-card__icon {
            font-size: 2.2rem;
            flex-shrink: 0;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-background);
            border-radius: 14px;
            box-shadow: inset 0 0 0 1px var(--border-color);
        }

        .asset-type-card__body {
            flex: 1;
            min-width: 0;
        }

        .asset-type-card__body h5 {
            margin: 0 0 0.3rem;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .asset-type-card__body p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .asset-type-card__check {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .asset-type-selector__hint {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            background: var(--light-bg);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .badge-prototype {
            font-size: 0.65rem;
            font-weight: 600;
            background: #fff3cd;
            color: #856404;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            border: 1px solid #ffc107;
            white-space: nowrap;
        }

        @media screen and (max-width: 640px) {
            .asset-type-cards {
                grid-template-columns: 1fr;
            }

            .asset-type-card {
                padding: 1rem 1.25rem;
            }

            .asset-type-card__icon {
                font-size: 1.8rem;
                width: 52px;
                height: 52px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function toggleExpiry() {
            const isSubscription = document.querySelector('input[name="license_type"]:checked')?.value === 'Berlangganan';
            const group = document.getElementById('expiryGroup');
            group.style.display = isSubscription ? 'flex' : 'none';
            document.getElementById('expiry_date').required = isSubscription;
        }
    </script>
@endpush
