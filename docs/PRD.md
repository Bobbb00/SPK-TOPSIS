# PRD: SPK Seleksi Beasiswa (Metode TOPSIS)

## 1. Tech Stack & Environment

- **Backend:** Laravel 11/12 (PHP 8.2+ dengan strict types)
- **Frontend:** Vue 3 (Composition API, `<script setup>`) via Inertia.js
- **Styling:** Tailwind CSS (Data-dense layout, slate/zinc theme)
- **Database:** MySQL 8.0 via Docker Compose (Port 3306)
- **Testing:** Pest PHP / PHPUnit

---

## 2. Rumus Matematis Engine (app/Services/TopsisService.php)

1. **Normalisasi:** $r_{ij} = \frac{x_{ij}}{\sqrt{\sum x_{kj}^2}}$ _(antisipasi pembagian dengan nol jika $\sum x^2 = 0$)_.
2. **Normalisasi Terbobot:** $y_{ij} = w_j \times r_{ij}$.
3. **Solusi Ideal Positif ($A^+$) & Negatif ($A^-$):**
   - Benefit: $A^+ = \max(y)$, $A^- = \min(y)$
   - Cost: $A^+ = \min(y)$, $A^- = \max(y)$
4. **Jarak Solusi Ideal:** $D_i^+ = \sqrt{\sum (y_{ij} - A_j^+)^2}$, $D_i^- = \sqrt{\sum (y_{ij} - A_j^-)^2}$.
5. **Skor Akhir Pendaftar:** $V_i = \frac{D_i^-}{D_i^+ + D_i^-}$ (skor 0.0 sampai 1.0).

---

## 3. Database Schema Requirements

- `users`: `id`, `name`, `email`, `password`, `timestamps`.
- `criterias`: `id`, `code`, `name`, `weight` (decimal 4,2), `type` (enum: 'benefit', 'cost'), `timestamps`.
- `applicants`: `id`, `nim` (unique), `name`, `study_program`, `timestamps`.
- `evaluations`: `id`, `applicant_id`, `criteria_id`, `score` (decimal 8,2), `timestamps` (Unique: `[applicant_id, criteria_id]`).
- `scholarship_quotas`: `id`, `quota_limit` (integer), `period`, `timestamps`.

---

## 4. Implementation Checklist (Sesuai 8 Modul Mindmap)

### Fase 1: Hasil & Ranking

- [x] **Tabel Peringkat:** Menampilkan tabel ranking hasil perhitungan terurut dari nilai $V_i$ terbesar.
- [x] **Tanda Rekomendasi Lolos:** Penanda status lolos otomatis bagi pendaftar yang masuk dalam kuota beasiswa.
- [x] **Cari & Saring Hasil:** Input pencarian pendaftar berdasarkan nama/NIM dan filter per prodi.

### Fase 2: Kriteria & Bobot

- [x] **Daftar Kriteria:** Halaman kelola data kriteria seleksi.
- [x] **Jenis Benefit / Cost:** Dropdown penentuan sifat kriteria (`benefit` / `cost`).
- [x] **Atur Bobot Kriteria:** Input bobot dengan validasi reaktif Vue (tombol submit disabled jika total bobot $\neq 1.0$ atau $100\%$).

### Fase 3: Data Pendaftar, Penilaian & Engine

- [x] **Data Pendaftar:**
  - [x] CRUD pendaftar (Daftar, Tambah, Ubah & Hapus Data).
- [x] **Input Penilaian:**
  - [x] Form nilai dinamis menyesuaikan kriteria aktif.
  - [x] Simpan & perbarui skor nilai per pendaftar.
  - [x] Peringatan nilai kosong (mencegah kalkulasi data yang tidak lengkap).
- [x] **Hitung Ranking Otomatis:**
  - [x] Service `app/Services/TopsisService.php` (normalisasi, pembobotan nilai, dan skor akhir).
  - [x] Unit test `tests/Unit/TopsisServiceTest.php` untuk validasi keakuratan rumus.
  - [x] Tombol aksi "Jalankan Perhitungan" dari antarmuka.

### Fase 4: Transparansi Perhitungan & Laporan

- [x] **Transparansi Perhitungan:**
  - [x] Tab langkah hitung bertahap (Matriks Normalisasi & Matriks Terbobot).
  - [x] Acuan nilai terbaik & terburuk (tampilan baris nilai $A^+$ dan $A^-$).
  - [x] Rincian skor per pendaftar (modal/drawer detail jarak $D^+$ dan $D^-$).
- [x] **Laporan & Ekspor:**
  - [x] Fitur unduh laporan hasil seleksi ke Excel (.xlsx / .csv dengan BOM).
  - [x] Fitur cetak/ekspor hasil keputusan ke PDF.
  - [x] Pilihan cakupan isi laporan (semua pendaftar atau hanya yang lolos kuota).

### Fase 5: Akun Panitia

- [x] **Masuk & Keluar:** Halaman login & logout panitia menggunakan session Laravel.
- [x] **Daftar Akun Panitia:** Tampilan daftar pengguna pengelola sistem.
- [x] **Kelola Akun:** Fitur tambah panitia baru dan update password.

---

## 5. Acceptance Criteria

- Seluruh 8 modul di atas dapat diakses sesuai batasan hak akses panitia.
- Hasil perhitungan matematis identik dengan perhitungan manual tanpa selisih floating point $> 0.0001$.
- UI bersih, padat data (_data-dense_), responsif, dan bebas elemen dekoratif yang berlebihan.
