# EcoRead E-Book Backend

## Backend API dan Admin Panel untuk Platform Literasi Digital EcoRead E-Book

EcoRead E-Book Backend adalah sistem backend berbasis Laravel yang digunakan untuk mengelola API, autentikasi pengguna, pengajuan akses e-book, proteksi file PDF, email notifikasi, serta admin panel menggunakan Laravel Filament.

Backend ini menjadi pusat pengelolaan data untuk aplikasi EcoRead E-Book, yaitu platform literasi digital berbasis web sebagai solusi akses pendidikan merata dan pengurangan limbah kertas bagi mahasiswa S1 Fakultas Ilmu Komputer Universitas Al-Khairiyah.

---

## Teknologi yang Digunakan

- Laravel
- Laravel Filament
- Laravel Sanctum / Token Authentication
- MySQL
- Laravel Mail
- Laravel Validation
- Laravel Middleware
- Laravel Controller
- Laravel Storage
- REST API

---

## Fitur Backend

### Autentikasi Pengguna

- Register pengguna
- Login pengguna
- Logout pengguna
- Verifikasi email
- Lupa password
- Reset password
- Token API untuk akses frontend
- Middleware autentikasi

### Manajemen Admin

- Admin panel menggunakan Laravel Filament
- Login admin
- Dashboard admin
- Kelola data e-book
- Kelola kategori e-book
- Kelola data pengguna
- Kelola pengajuan akses e-book
- Approve atau reject pengajuan akses
- Monitoring status pengajuan

### Manajemen E-Book

- Tambah data e-book
- Edit data e-book
- Hapus data e-book
- Upload file PDF
- Menampilkan katalog e-book melalui API
- Menampilkan detail e-book melalui API
- Proteksi file PDF agar tidak bisa diakses sembarang user

### Pengajuan Akses E-Book

- User dapat mengajukan akses e-book
- Admin dapat memverifikasi pengajuan
- Status pengajuan terdiri dari pending, approved, dan rejected
- User hanya dapat melihat pengajuan miliknya sendiri
- User tidak dapat melihat data pengajuan user lain
- Tombol baca/download pada frontend hanya aktif jika pengajuan disetujui

### Email Notification

- Email registrasi
- Email verifikasi akun
- Email pengajuan akses
- Email update status pengajuan
- Email reset password

---

## Keamanan Sistem

Backend EcoRead E-Book menerapkan beberapa mekanisme keamanan, yaitu:

- Validasi input pada setiap request
- Middleware autentikasi API
- Token-based authentication
- Proteksi route menggunakan middleware
- Pembatasan akses data berdasarkan user login
- User A tidak dapat melihat data pengajuan milik User B
- File PDF hanya dapat diakses oleh user yang sudah disetujui
- Admin panel terpisah menggunakan Laravel Filament
- Validasi upload file PDF
- Proteksi data sensitif melalui konfigurasi environment
- Password disimpan dalam bentuk hash
- Reset password menggunakan mekanisme token email

---

## Hak Akses Pengguna

### User / Mahasiswa

User memiliki akses untuk:

- Register dan login
- Melihat katalog e-book
- Melihat detail e-book
- Mengajukan akses e-book
- Melihat status pengajuan miliknya sendiri
- Membaca atau mengunduh PDF jika pengajuan disetujui

### Admin

Admin memiliki akses penuh melalui Laravel Filament untuk:

- Mengelola data user
- Mengelola data e-book
- Mengelola kategori
- Mengelola pengajuan akses
- Menyetujui atau menolak pengajuan
- Mengelola file PDF
- Melihat data sistem melalui dashboard admin

---

## Alur Sistem Backend

1. User melakukan register melalui frontend.
2. Backend melakukan validasi data register.
3. Sistem mengirim email verifikasi.
4. User login dan mendapatkan token API.
5. User melihat katalog e-book melalui API.
6. User memilih e-book dan mengajukan akses.
7. Backend menyimpan data pengajuan dengan status pending.
8. Admin membuka panel Filament dan memverifikasi pengajuan.
9. Admin menyetujui atau menolak pengajuan.
10. Sistem mengirim email update status pengajuan.
11. Jika disetujui, user dapat membaca atau mengunduh PDF.
12. Backend memastikan PDF hanya dapat diakses oleh user yang memiliki izin.

---

## Struktur Project

```bash
e-book_backend/
├── app/
│   ├── Filament/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Mail/
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── storage/
├── public/
├── .env.example
├── composer.json
└── README.md
```

---

## Instalasi Project

Clone repository:

```bash
git clone https://github.com/BayDevelopment/e-book_backend.git
```

Masuk ke folder project:

```bash
cd e-book_backend
```

Install dependency Laravel:

```bash
composer install
```

Copy file environment:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Atur konfigurasi database pada file `.env`:

```env
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration:

```bash
php artisan migrate
```

Jalankan seeder jika tersedia:

```bash
php artisan db:seed
```

Jalankan storage link jika dibutuhkan:

```bash
php artisan storage:link
```

Jalankan server Laravel:

```bash
php artisan serve
```

---

## Konfigurasi Email

Atur konfigurasi email pada file `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@example.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email@example.com
MAIL_FROM_NAME="EcoRead E-Book"
```

---

## Konfigurasi API

Frontend Vue.js akan terhubung ke backend melalui endpoint API Laravel.

Contoh base URL API:

```env
http://127.0.0.1:8000/api
```

---

## Contoh Endpoint API

Beberapa endpoint utama backend:

```bash
POST /api/register
POST /api/login
POST /api/logout
GET  /api/books
GET  /api/books/{id}
POST /api/access-requests
GET  /api/my-access-requests
GET  /api/books/{id}/read
GET  /api/books/{id}/download
```

Endpoint dapat disesuaikan dengan struktur route yang digunakan pada project.

---

## Validasi Backend

Validasi dilakukan pada sisi backend untuk menjaga keamanan dan konsistensi data.

Contoh validasi yang diterapkan:

- Validasi nama pengguna
- Validasi email
- Validasi password
- Validasi file PDF
- Validasi data e-book
- Validasi pengajuan akses
- Validasi status pengajuan
- Validasi akses berdasarkan user login

---

## Proteksi PDF

File PDF tidak diberikan akses langsung secara publik. Sistem melakukan pengecekan terlebih dahulu sebelum file dapat dibaca atau diunduh.

Ketentuan akses PDF:

- User harus login
- User harus memiliki pengajuan akses
- Status pengajuan harus approved
- User hanya bisa mengakses file miliknya sendiri
- Jika tidak memiliki izin, sistem menolak akses

---

## Keterkaitan dengan SDGs

### SDG 4 - Pendidikan Berkualitas

Backend mendukung penyediaan akses literasi digital yang terstruktur dan mudah digunakan oleh mahasiswa.

### SDG 10 - Mengurangi Kesenjangan

Sistem membantu pemerataan akses referensi pembelajaran digital bagi mahasiswa S1 Fakultas Ilmu Komputer Universitas Al-Khairiyah.

### SDG 12 - Konsumsi dan Produksi yang Bertanggung Jawab

Sistem mendukung penggunaan dokumen digital untuk mengurangi ketergantungan terhadap buku fisik dan penggunaan kertas.

---

## Status Project

Project ini dikembangkan untuk mengikuti:

**Web Technology Competition OLIVIA XI 2026**

Tema:

**Developing Sustainable Digital Solutions to Support the Sustainable Development Goals (SDGs)**

---

## Tim Pengembang

**Nama Tim:** EcoRead Team  
**Ketua Tim / Pengembang Utama:** Nama Kamu  
**Pembimbing:** Didda Rahayu Yuliana, M.Kom

**Institusi:** Fakultas Ilmu Komputer, Universitas Al-Khairiyah  
**Kompetisi:** Web Technology Competition OLIVIA XI 2026

---

## Catatan Keamanan Repository

File berikut tidak boleh ikut diunggah ke repository:

- `.env`
- `vendor/`
- `node_modules/`
- file konfigurasi sensitif
- password email asli
- credential database production

Pastikan file `.gitignore` sudah dikonfigurasi dengan benar.

---

## Lisensi

Project ini dibuat untuk kebutuhan kompetisi dan pengembangan solusi literasi digital berbasis web.
