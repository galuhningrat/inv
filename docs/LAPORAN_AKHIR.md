# LAPORAN AKHIR & DOKUMENTASI SERAH TERIMA

## Modul: Manajemen Aset Non-Fisik (SINADAS) — STTI Cirebon

> **Versi:** 1.0.0  
> **Tanggal Rilis:** 26 Oktober 2026  
> **Status:** ✅ Production-Ready  
> **Test Coverage:** 32/32 PASSED (67 Assertions)

---

## 📑 Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Laporan Audit Awal](#2-laporan-audit-awal)
3. [Matriks Field Final](#3-matriks-field-final)
4. [Rancangan Database](#4-rancangan-database)
5. [Implementasi Fitur](#5-implementasi-fitur)
6. [Hasil Pengujian](#6-hasil-pengujian)
7. [Strategi Migrasi Database](#7-strategi-migrasi-database)
8. [Panduan Pengisian (End-User)](#8-panduan-pengisian-end-user)
9. [Panduan Developer (Maintenance)](#9-panduan-developer-maintenance)
10. [Changelog](#10-changelog)

---

## 1. Ringkasan Eksekutif

Modul Manajemen Aset Non-Fisik adalah bagian dari sistem SINADAS (Sistem Inventaris dan Administrasi Aset) STTI Cirebon. Modul ini menangani 9 kategori aset tak berwujud: **Software**, **Cloud/SaaS**, **Akun Digital**, **Domain/Hosting**, **Jurnal Ilmiah**, **HAKI/Paten**, **Sertifikat Digital**, **Kurikulum**, dan **Lainnya**.

### ✨ Fitur Utama

- ✅ Formulir **dinamis** yang menyesuaikan field per kategori
- ✅ **Enkripsi otomatis** untuk data sensitif (`product_key`)
- ✅ **Audit trail** lengkap (siapa, kapan, apa yang diubah)
- ✅ **Pencarian lanjutan** termasuk di dalam atribut JSON
- ✅ **Kode inventaris otomatis** (`NF/YYYY/NNNN`) yang aman dari _race condition_
- ✅ **Keamanan file** dengan penyimpanan privat + otorisasi unduh
- ✅ **Pengingat kedaluwarsa** otomatis via _cron job_

---

## 2. Laporan Audit Awal

### 2.1 Struktur Source Code yang Ditemukan

| Komponen       | Sebelum                 | Sesudah                                      |
| -------------- | ----------------------- | -------------------------------------------- |
| **Controller** | CRUD statis             | Dinamis, tervalidasi, dengan retry mechanism |
| **Model**      | Skema datar             | Skema dinamis berbasis `getSchema()`         |
| **Views**      | Formulir HTML hardcoded | Dirender via JavaScript + JSON Schema        |

### 2.2 Masalah Terverifikasi Sebelum Perbaikan

| #   | Masalah                                      | Dampak                                     |
| --- | -------------------------------------------- | ------------------------------------------ |
| 1   | Formulir statis tidak cocok untuk 9 kategori | User bingung, field tidak relevan muncul   |
| 2   | File sertifikat disimpan di `disk('public')` | Rawan diakses publik tanpa otorisasi       |
| 3   | `product_key` disimpan sebagai teks biasa    | Data sensitif bisa bocor via SQL injection |
| 4   | Tidak ada validasi unik untuk `asset_code`   | Risiko duplikasi saat concurrent request   |
| 5   | Tidak ada riwayat perubahan                  | Tidak bisa audit siapa mengubah apa        |
| 6   | Tidak ada pencarian JSON                     | Sulit menemukan aset via atribut spesifik  |

---

## 3. Matriks Field Final

### 3.1 Software

| Field                 | Tipe   | Wajib | Validasi                            |
| --------------------- | ------ | ----- | ----------------------------------- |
| `product_name`        | text   | ✅    | max:255                             |
| `publisher`           | text   | ❌    | max:255                             |
| `version`             | text   | ❌    | -                                   |
| `license_model`       | select | ✅    | in:Per User,Per Device,Site License |
| `product_key`         | text   | ❌    | **Encrypted**                       |
| `assigned_user_email` | email  | ❌    | valid email                         |

### 3.2 Cloud/SaaS

| Field             | Tipe   | Wajib | Validasi           |
| ----------------- | ------ | ----- | ------------------ |
| `service_name`    | text   | ✅    | -                  |
| `product_package` | text   | ✅    | -                  |
| `user_quota`      | number | ❌    | min:0              |
| `billing_period`  | select | ❌    | in:Bulanan,Tahunan |

### 3.3 Akun Digital

| Field           | Tipe  | Wajib | Validasi    |
| --------------- | ----- | ----- | ----------- |
| `account_name`  | text  | ✅    | -           |
| `account_email` | email | ✅    | valid email |
| `account_type`  | text  | ✅    | -           |

### 3.4 Domain/Hosting

| Field                | Tipe | Wajib | Validasi |
| -------------------- | ---- | ----- | -------- |
| `domain_name`        | text | ✅    | -        |
| `registrar`          | text | ✅    | -        |
| `domain_expiry_date` | date | ❌    | -        |
| `hosting_end_date`   | date | ❌    | -        |

### 3.5 Jurnal Ilmiah

| Field          | Tipe | Wajib | Validasi |
| -------------- | ---- | ----- | -------- |
| `journal_name` | text | ✅    | -        |
| `issn`         | text | ❌    | -        |
| `coverage`     | text | ❌    | -        |

### 3.6 HAKI/Paten

| Field                 | Tipe   | Wajib | Validasi                                 |
| --------------------- | ------ | ----- | ---------------------------------------- |
| `work_name`           | text   | ✅    | -                                        |
| `ip_type`             | select | ✅    | in:Paten,Hak Cipta,Merek,Desain Industri |
| `registration_number` | text   | ✅    | -                                        |
| `inventor`            | text   | ✅    | -                                        |
| `right_holder`        | text   | ✅    | -                                        |

### 3.7 Sertifikat Digital

| Field            | Tipe | Wajib | Validasi |
| ---------------- | ---- | ----- | -------- |
| `cert_name`      | text | ✅    | -        |
| `cert_type`      | text | ✅    | -        |
| `issuer`         | text | ✅    | -        |
| `serial_number`  | text | ✅    | -        |
| `subject_domain` | text | ✅    | -        |

### 3.8 Kurikulum

| Field             | Tipe | Wajib | Validasi |
| ----------------- | ---- | ----- | -------- |
| `curriculum_name` | text | ✅    | -        |
| `study_program`   | text | ✅    | -        |
| `version`         | text | ✅    | -        |
| `sk_number`       | text | ❌    | -        |

### 3.9 Lainnya

| Field          | Tipe     | Wajib | Validasi |
| -------------- | -------- | ----- | -------- |
| `sub_category` | text     | ✅    | -        |
| `reason`       | textarea | ✅    | -        |

> 📌 **Catatan:** Semua field khusus di atas disimpan dalam kolom `category_data` (JSON) pada tabel `intangible_assets`.

---

## 4. Rancangan Database

### 4.1 Keputusan Desain: JSON Terstruktur

**Alasan memilih Pendekatan JSON:**

1. ✅ Skema kategori **relatif stabil** (9 kategori tetap)
2. ✅ Menghindari **50+ kolom NULL** di tabel utama
3. ✅ Penambahan field baru **tidak butuh migrasi** besar
4. ✅ Validasi tetap ketat via **Schema di Model** + **Validator di Controller**

### 4.2 Struktur Tabel Utama

```sql
intangible_assets
├── id (PK)
├── asset_code (VARCHAR, UNIQUE)        -- Auto-generated
├── name (VARCHAR)
├── description (TEXT)
├── category (VARCHAR)                   -- 9 kategori
├── category_data (JSON)                 -- Atribut dinamis
├── vendor (VARCHAR, NULLABLE)
├── price (DECIMAL)
├── acquisition_method (VARCHAR)
├── activation_date (DATE)
├── funding_source (VARCHAR)
├── contract_number (VARCHAR)
├── license_type (VARCHAR)
├── billing_model (VARCHAR)
├── expiry_date (DATE, NULLABLE)
├── reminder_days (INT, NULLABLE)
├── quota (VARCHAR)
├── unit_id (FK → units)
├── pic_id (FK → users)
├── access_url (VARCHAR)
├── certificate_file (VARCHAR)           -- Path privat
├── product_key (TEXT)                   -- Encrypted
├── status (VARCHAR)
├── created_by (FK → users)
├── asset_request_id (FK → asset_requests)
└── timestamps + softDeletes
```

### 4.3 Tabel Audit Trail

```sql
intangible_asset_logs
├── id (PK)
├── intangible_asset_id (FK)
├── user_id (FK → users)
├── action (VARCHAR)                     -- created/updated/deleted
├── changes (JSON)                       -- {field: {old, new}}
├── description (VARCHAR)
└── timestamps
```

### 4.4 Keamanan Data

| Data Sensitif      | Metode Perlindungan                                        |
| ------------------ | ---------------------------------------------------------- |
| `product_key`      | Laravel encrypted cast (AES-256)                           |
| `certificate_file` | Disimpan di `disk('local')`, akses via Controller          |
| File upload        | Validasi MIME + size + unique filename (Laravel auto-hash) |

---

## 5. Implementasi Fitur

### 5.1 Form Dinamis

- **Backend:** `IntangibleAsset::getSchema($category)` mengembalikan definisi field.
- **Frontend:** JavaScript merender field berdasarkan schema yang dipilih.
- **Aksesibilitas:** Setiap field punya id dan `<label for="...">`.

### 5.2 Kode Inventaris Otomatis

- **Format:** `NF/YYYY/NNNN` (contoh: `NF/2026/0001`)
- **Keamanan:** Menggunakan `lockForUpdate()` + retry mechanism (3x).
- **Immutable:** Kode tidak bisa diubah setelah dibuat (`unset($validated['asset_code'])` di update).

### 5.3 Keamanan File

- **Upload:** Validasi mimes + mimetypes + max:5120.
- **Storage:** Disk local (bukan public).
- **Access:** Route khusus `downloadCertificate()` dengan cek otorisasi.
- **Anti-orphan:** File lama baru dihapus setelah DB sukses update.

### 5.4 Audit Trail

- **Event:** created, updated, deleted via Model `booted()`.
- **Detail:** Mencatat field yang berubah + nilai before/after.
- **UI:** Timeline di halaman detail aset.

### 5.5 Pengingat Kedaluwarsa

- **Cron:** `php artisan intangible:check-expiry` (daily).
- **Visual:** Badge warna di index (Kritis/Segera/Kedaluwarsa).
- **Widget:** Dashboard menampilkan aset yang mendekati kedaluwarsa.

---

## 6. Hasil Pengujian

### 6.1 Ringkasan

- **Total Test Case:** 32
- **Status:** ✅ 32 PASSED, 0 FAILED
- **Total Assertions:** 67
- **Durasi:** ±90 detik

### 6.2 Cakupan Test

| Grup                      | Jumlah Test | Status  |
| ------------------------- | ----------- | ------- |
| Validasi Dasar & Kategori | 13          | ✅ PASS |
| File Upload               | 5           | ✅ PASS |
| Kode Aset                 | 3           | ✅ PASS |
| Reminder                  | 3           | ✅ PASS |
| Authorization             | 2           | ✅ PASS |
| Edit & Detail             | 2           | ✅ PASS |
| Field Sensitif            | 1           | ✅ PASS |
| Link Pengadaan            | 1           | ✅ PASS |
| Lainnya (existing)        | 2           | ✅ PASS |

### 6.3 Cara Menjalankan Test

```bash
# 1. Pastikan database testing ada
psql -U postgres -c "CREATE DATABASE inv_stti_testing;"

# 2. Jalankan semua test
php artisan test

# 3. Atau test spesifik
php artisan test tests/Feature/IntangibleAssetTest.php
```

---

## 7. Strategi Migrasi Database

### 7.1 Prinsip Umum

1. ✅ Semua migrasi bersifat aditif (tidak destruktif).
2. ✅ Tidak menghapus kolom lama meski tidak dipakai formulir.
3. ✅ Tidak menjalankan `migrate:fresh` di produksi.
4. ✅ Data historis (`asset_code`, timestamps, FK) dipertahankan.

### 7.2 Migrasi yang Sudah Dijalankan

| Migration                                 | Tujuan                                  | Reversibel |
| ----------------------------------------- | --------------------------------------- | ---------- |
| `add_dynamic_fields_to_intangible_assets` | Tambah `category_data`, `billing_model` | ✅         |
| `add_description_and_acquisition_method`  | Tambah 2 kolom baru                     | ✅         |
| `add_administrative_statuses`             | Ubah tipe status                        | ✅         |
| `create_intangible_asset_logs_table`      | Tabel audit                             | ✅         |
| `update_license_type_constraint`          | Drop CHECK constraint lama              | ✅         |
| `normalize_legacy_license_type_values`    | Map Berlangganan → Subscription         | ⚠️ One-way |
| `normalize_legacy_status_values`          | Map Nonaktif → Dihentikan               | ⚠️ One-way |

### 7.3 Strategi Rollback

#### Skenario A: Migrasi Skema Gagal

```bash
php artisan migrate:rollback --step=1
```

#### Skenario B: Migrasi Data Gagal

```bash
# Restore dari backup (PowerShell)
psql -U postgres -d inv_stti -f backup_before_migration.sql
```

#### Skenario C: File Sertifikat Korup

```bash
# File asli di disk public tetap utuh (copy → verify → delete)
php artisan intangible:migrate-certificates
```

### 7.4 Checklist Sebelum Deploy Produksi

- [ ] Backup DB: `pg_dump inv_stti > backup_YYYYMMDD.sql`
- [ ] Backup folder `storage/app/public/intangible-certificates`
- [ ] Jalankan `php artisan migrate --pretend` untuk review
- [ ] Jalankan `php artisan migrate`
- [ ] Jalankan `php artisan intangible:migrate-certificates --dry-run`
- [ ] Verifikasi hasil, lalu jalankan tanpa `--dry-run`
- [ ] Verifikasi 3 sampel aset lama
- [ ] Jalankan `php artisan test` → 100% PASS

### 7.5 Yang TIDAK Boleh Dilakukan ❌

- ❌ `php artisan migrate:fresh`
- ❌ `php artisan migrate:reset`
- ❌ Menghapus kolom lama
- ❌ Mengganti `asset_code` yang sudah ada
- ❌ Drop tabel `intangible_asset_logs`

---

## 8. Panduan Pengisian (End-User)

### 8.1 Langkah Pengisian

1. Pilih **Kategori Aset** terlebih dahulu → formulir otomatis menyesuaikan.
2. Isi **Nama Aset** dan **Vendor** (kecuali kategori HAKI/Kurikulum).
3. Pilih **Jenis Lisensi**:
    - Perpetual → tidak perlu tanggal kedaluwarsa.
    - Subscription / Hak Terdaftar → wajib isi tanggal kedaluwarsa.
4. Isi **Spesifikasi Khusus** sesuai panduan yang muncul.
5. Unggah **Dokumen pendukung** (PDF/JPG/PNG, max 5MB).
6. Tautkan ke **Pengadaan** (opsional) jika aset berasal dari pengajuan.

### 8.2 Tips & Kesalahan Umum

| ❌ Salah                          | ✅ Benar                             |
| --------------------------------- | ------------------------------------ |
| Isi vendor untuk HAKI             | Biarkan kosong (otomatis di-handle)  |
| Pilih Subscription tanpa tanggal  | Selalu isi tanggal kedaluwarsa       |
| Upload file > 5MB                 | Kompres dulu atau gunakan format PDF |
| Isi `product_key` terang-terangan | Data akan terenkripsi otomatis       |

---

## 9. Panduan Developer (Maintenance)

### 9.1 Struktur File Kunci

```text
app/
├── Console/Commands/
│   ├── CheckIntangibleAssetExpiry.php     # Cron pengingat
│   └── MigrateLegacyCertificates.php      # Migrasi file
├── Http/Controllers/
│   └── IntangibleAssetController.php      # CRUD utama
├── Models/
│   ├── IntangibleAsset.php                # Schema + relasi
│   └── IntangibleAssetLog.php             # Audit trail
└── ...
database/
├── factories/
│   ├── IntangibleAssetFactory.php
│   └── UnitFactory.php
└── migrations/
    └── ... (5 migrasi baru)
resources/views/intangible-assets/
├── create.blade.php    # Form tambah
├── edit.blade.php      # Form edit
├── index.blade.php     # Daftar
└── show.blade.php      # Detail + audit trail
tests/Feature/
└── IntangibleAssetTest.php   # 30+ test case
```

### 9.2 Cara Menambah Kategori Baru

1. Edit `IntangibleAsset::CATEGORIES` — tambah kategori baru.
2. Edit `IntangibleAsset::getSchema()` — definisikan field-nya.
3. Update `IntangibleAssetController` — tidak perlu, sudah dinamis.
4. Update view `create.blade.php` — tidak perlu, sudah dinamis.
5. Test — pastikan kategori baru muncul & validasi jalan.

### 9.3 Command Berguna

```bash
# Cek aset yang mendekati kedaluwarsa
php artisan intangible:check-expiry

# Migrasi file sertifikat lama (dari public ke local)
php artisan intangible:migrate-certificates --dry-run

# Jalankan test
php artisan test --filter=IntangibleAssetTest

# Clear cache
php artisan optimize:clear
```

### 9.4 Setup Cron Job (Produksi)

Tambahkan di crontab server:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Pastikan `routes/console.php` berisi:

```php
Schedule::command('intangible:check-expiry')->daily();
```

---

## 10. Changelog

### v1.0.0 (2026-10-26) — Initial Release

- ✅ Implementasi formulir dinamis 9 kategori
- ✅ JSON Schema-based form rendering
- ✅ Auto-generate kode aset `NF/YYYY/NNNN`
- ✅ Enkripsi `product_key`
- ✅ File storage pindah ke `disk('local')` (privat)
- ✅ Audit trail lengkap
- ✅ Pencarian JSON lanjutan
- ✅ Pengingat kedaluwarsa otomatis
- ✅ Integrasi dengan modul pengadaan
- ✅ 32 test case passing (67 assertions)

---

## 📞 Kontak & Dukungan

| Role              | Nama         | Kontak              |
| ----------------- | ------------ | ------------------- |
| **Developer**     | Tim IT STTI  | akademik@stti.ac.id |
| **Product Owner** | Sarpras STTI | -                   |

---

_Dokumentasi ini dibuat sebagai bagian dari serah terima Modul Manajemen Aset Non-Fisik SINADAS. Untuk pertanyaan teknis, silakan hubungi tim developer._
