# SystemPenilaianPPL

**Sistem Rekapitulasi & Penilaian Praktik Pengalaman Lapangan (PPL)**  
*Fakultas Ekonomi dan Bisnis (FEB) - Universitas Kuningan*

Aplikasi web modern berbasis **Laravel 11** dan **FilamentPHP v3/v5** yang dibangun khusus untuk mengelola, merekapitulasi, dan mengkalkulasi penilaian PPL mahasiswa Fakultas Ekonomi dan Bisnis Universitas Kuningan secara transparan, otomatis, dan terisolasi per Dosen Pembimbing Lapangan (DPL).

---

## 🌟 Fitur Utama

### 1. Dashboard & Statistik Real-Time
- Statistik live total DPL aktif, total mitra/instansi, total kelompok PPL, dan total mahasiswa PPL.
- Monitoring persentase penyelesaian penilaian per kelompok dan rincian mahasiswa per program studi:
  - S1 Manajemen (326 Mahasiswa)
  - S1 Akuntansi (57 Mahasiswa)
  - S1 Bisnis Digital (10 Mahasiswa)

### 2. Role-Based Access Control (RBAC) Ketat
- **Administrator Fakultas / Prodi:**
  - Akses penuh ke seluruh menu dan data.
  - Manajemen akun DPL & Admin (tambah, edit, hapus, reset password).
  - Manajemen data master Mitra / Instansi (SKPD, Swasta, UMKM, Desa, BUMN).
  - Manajemen Kelompok PPL dan penugasan DPL pembimbing.
  - Hak khusus membuka kunci (*unlock*) nilai mahasiswa jika terdapat revisi.
  - Backup & Restore database langsung dari antarmuka web.
- **Dosen Pembimbing Lapangan (DPL):**
  - Hanya dapat melihat kelompok dan mahasiswa yang dibimbingnya sendiri (`dpl_id = auth()->id()`).
  - Menu sensitif (User Management, Mitra Management, Backup DB) otomatis disembunyikan dan diblokir (*403 Forbidden*).

### 3. Kalkulasi Nilai Otomatis & Standar Konversi Mutu
- **Formula Nilai Akhir:**
  $$\text{Nilai Akhir} = (\text{Nilai Mitra} \times 60\%) + (\text{Nilai Laporan DPL} \times 40\%)$$
- **Standar Konversi Huruf Mutu:**
  - **A** : 81,0 – 100 *(Sangat Baik)*
  - **AB** : 75,0 – 80,9 *(Baik Sekali)*
  - **B** : 69,0 – 74,9 *(Baik)*
  - **BC** : 63,0 – 68,9 *(Cukup Baik)*
  - **C** : 57,0 – 62,9 *(Cukup)*
  - **CD** : 51,0 – 56,9 *(Kurang)*
  - **D** : 45,0 – 50,9 *(Sangat Kurang)*
  - **E** : 0 – 44,9 *(Tidak Lulus)*
- Perhitungan otomatis berjalan pada model lifecycle (`saving` hook) dan *live reactive preview* pada form pop-up input nilai.

### 4. Ekspor & Impor Microsoft Excel Asli (`.xlsx`)
- Menggunakan pustaka resmi `phpoffice/phpspreadsheet`.
- **Export Rekap Nilai (.xlsx):** Berkas Excel lengkap dengan kop resmi fakultas, nomor NIM berformat teks (angka nol depan tidak hilang), desimal dua digit, dan auto-sizing kolom.
- **Import Mahasiswa (.xlsx):** Mendukung unggah berkas Excel massal dengan deteksi kolom otomatis (*NIM, Nama, Prodi, Kelompok, Mitra, DPL, Tahun Akademik*) dan penanganan duplikasi cerdas (*upsert*).
- **Download Template (.xlsx):** Template baku siap pakai dengan contoh baris data riil.

### 5. Modul Backup & Restore Database (Khusus Admin)
- **Buat Backup Baru (SQL):** Menghasilkan berkas `.sql` universal (`DROP TABLE`, `CREATE TABLE`, `INSERT INTO`) yang siap diimpor ke cPanel / phpMyAdmin / MySQL di server hosting.
- **Download Database (.sqlite):** Salinan langsung berkas database lokal.
- **Restore via Web UI:** Mengunggah dan memulihkan berkas cadangan langsung dari browser.
- Riwayat berkas tersimpan aman di `storage/app/backups/`.

### 6. Desain Antarmuka Identik Portal FEB UNIKU
- Tata letak layar penuh (*Full Screen Width*) mengikuti resolusi monitor (1080p, 2K, ultrawide).
- Fitur ciutkan sidebar (*collapsible sidebar*) di desktop.
- Halaman login elegan identik dengan portal FEB UNIKU (`Plus Jakarta Sans`, logo resmi, bantuan WhatsApp untuk lupa password).

---

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel 11.x
- **Admin Panel:** FilamentPHP v3/v5 (TALL Stack: Tailwind, Alpine.js, Laravel, Livewire)
- **Spreadsheet Engine:** PhpSpreadsheet (`phpoffice/phpspreadsheet`)
- **Database Default:** SQLite (Lokal) / Kompatibel Penuh dengan MySQL & MariaDB
- **Testing:** PHPUnit 11

---

## 🚀 Panduan Instalasi & Menjalankan

### 1. Clone Repositori
```bash
git clone https://github.com/adimhsd/SystemPenilaianPPL.git
cd SystemPenilaianPPL
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrasi & Seeding Data Riil
```bash
php artisan migrate:fresh --seed
```
*Seeder otomatis memasukkan 1 Akun Admin, 41 Dosen Pembimbing (DPL), 81 Mitra/Instansi, 78 Kelompok PPL, dan 393 Mahasiswa riil.*

### 5. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban di: **`http://localhost:8000/login`** (atau langsung di root **`http://localhost:8000`**)

---

## 🔑 Kredensial Login Pengujian

| Role | Username | Password | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `password` | Akses penuh seluruh modul dan konfigurasi |
| **DPL (Contoh 1)** | `DPL_PPL01` | `password` | Dr. Dede Djuniardi, S.E., M.M. |
| **DPL (Contoh 2)** | `DPL_PPL02` | `password` | Yudi Febriansyah, S.E., M.M. |
| **DPL (Lainnya)** | `DPL_PPL03` s/d `DPL_PPL41` | `password` | Seluruh 41 Dosen Pembimbing Lapangan |

---

## 🧪 Pengujian Otomatis (Automated Tests)

Jalankan seluruh rangkaian pengujian fitur dan unit:
```bash
php artisan test
```
*Seluruh 15 pengujian (79 assertions) lolos 100% (PASS).*

---

## 📄 Lisensi
Hak Cipta © 2026 Fakultas Ekonomi dan Bisnis (FEB) Universitas Kuningan.  
Dikembangkan untuk keperluan akademik dan penilaian Praktik Pengalaman Lapangan (PPL).
