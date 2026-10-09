# Sistem Pendukung Keputusan (SPK) Beasiswa — Metode TOPSIS

Aplikasi web Sistem Pendukung Keputusan (SPK) untuk penentuan penerima beasiswa prestasi & afirmasi mahasiswa menggunakan metode **TOPSIS** (*Technique for Order of Preference by Similarity to Ideal Solution*). Dilengkapi dengan fitur transparansi perhitungan matriks dan **AI Explanation** (Groq Cloud / Google Gemini) untuk menjelaskan alasan kelulusan/cadangan dalam bahasa yang komunikatif dan ramah publik.

---

## 🚀 Tech Stack

- **Backend:** Laravel 12 (PHP 8.4)
- **High-Performance Server:** [Laravel Octane](https://laravel.com/docs/octane) dengan [FrankenPHP](https://frankenphp.dev/)
- **Frontend:** Vue 3, Inertia.js, Vite
- **Database & Cache:** MySQL 8.0, Redis (Alpine)
- **AI Engine:** Groq Cloud API (`qwen/qwen3.8-27b`) & Google Gemini (`gemini-flash-latest`) sebagai fallback, plus Heuristic Engine bawaan.

---

## 📋 Fitur Utama

1. **Dashboard & Statistik:** Ringkasan kuota, kelengkapan nilai, dan status kelayakan perhitungan.
2. **Manajemen Kriteria & Bobot:** Pengaturan sifat kriteria (Benefit / Cost) dan validasi total bobot 100%.
3. **Data Pendaftar & Penilaian:** Input data mahasiswa dan matriks evaluasi kriteria.
4. **Kalkulasi TOPSIS Otomatis:** Perhitungan matriks ternormalisasi, matriks terbobot, solusi ideal (A+ / A-), jarak euclidean (D+ / D-), dan nilai preferensi ($V_i$).
5. **Penjelasan Keputusan Berbasis AI:** Narasi otomatis penjelasan hasil seleksi tanpa rumus matematika rumit bagi mahasiswa dan wali.
6. **Ekspor & Cetak Laporan:** Rekapitulasi hasil pemeringkatan siap cetak.

---

## 🛠️ Panduan Instalasi & Menjalankan (Docker)

### Prasyarat
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (sudah berjalan)
- [Node.js](https://nodejs.org/) (opsional jika ingin build aset di host)

### Langkah Setup

1. **Clone repositori:**
   ```bash
   git clone <url-repositori-anda>
   cd SPK
   ```

2. **Siapkan environment file:**
   ```bash
   cp .env.example .env
   ```
   > Isi `GROQ_API_KEY` atau `GEMINI_API_KEY` di file `.env` jika ingin mengaktifkan narasi AI online. Jika dikosongkan, sistem otomatis menggunakan Smart Heuristic Engine offline.

3. **Install dependensi Composer & binary Octane FrankenPHP:**
   ```bash
   docker compose run --rm app composer install
   docker compose run --rm app php artisan key:generate
   docker compose run --rm app php artisan octane:install --server=frankenphp
   ```

4. **Jalankan container Docker:**
   ```bash
   docker compose up -d
   ```

5. **Jalankan Migrasi & Database Seeder:**
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```

6. **Build Frontend Assets (jika belum ter-build):**
   ```bash
   npm install
   npm run build
   ```

7. **Akses Aplikasi:**
   Buka browser dan kunjungi: **`http://localhost:8000`**

---

## 🔐 Akun Default (Seeder)

Setelah menjalankan `db:seed`, akun panitia seleksi bawaan adalah:
- **Email:** `admin@spk.test`
- **Password:** `password`

---

## 🧪 Menjalankan Automated Tests

Aplikasi ini dilengkapi 60+ automated feature and unit tests untuk menjamin akurasi perhitungan matematis TOPSIS dan alur sistem:

```bash
# Menjalankan test via Docker:
docker compose exec app php artisan test

# Atau di lingkungan lokal host (jika PHP terpasang):
php artisan test
```

---

## 📂 Struktur Proyek Utama

- `app/Services/TopsisService.php` — Inti algoritma perhitungan matematika TOPSIS.
- `app/Services/AiExplanationService.php` — Layanan narasi keputusan & integrasi LLM (Groq / Gemini) + caching Redis.
- `app/Http/Controllers/` — Endpoint dashboard, kriteria, penilaian, dan ranking.
- `resources/js/Pages/` — Halaman antarmuka pengguna (Inertia + Vue 3).
- `docker-compose.yml` & `Dockerfile` — Konfigurasi lingkungan kontainer Docker.

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis & sistem pendukung keputusan internal. Open-source di bawah lisensi [MIT](LICENSE).
