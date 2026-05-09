# Ayu Sihombing
**Email:** ayuusihombing12@gmail.com

## HRM System

Project ini dibuat sebagai technical test aplikasi CRUD HR berbasis web menggunakan PHP 7.3.24, Laravel 8, dan PostgreSQL. Fitur utama mencakup CRUD Department dan Employee, import Attendance dari file Excel .xlsx dengan preview sebelum penyimpanan, serta dilengkapi validasi data, relasi tabel, filter attendance, paginasi, template import, dan seed data untuk mempermudah pengujian aplikasi.

## Gambaran Singkat Project

HRM System ini dipakai untuk mengelola data karyawan dan absensi dalam satu alur yang rapi.

Fitur yang tersedia:
- Kelola data department: tambah, lihat, ubah, dan hapus department.
- Kelola data employee: tambah, lihat, ubah, dan hapus data karyawan.
- Kelola data attendance: melihat daftar absensi, filter berdasarkan tanggal, dan import data dari file Excel.
- Preview data attendance sebelum masuk ke database.
- Deteksi NIK yang tidak ditemukan saat preview import.
- Deteksi data absensi duplikat agar tidak tersimpan dua kali.
- Download template Excel agar format import tetap konsisten.

## Teknologi yang Dipakai

- PHP
- Laravel 8
- PostgreSQL
- Blade Template
- Bootstrap 5
- JavaScript vanilla untuk parsing file `.xlsx` di browser

## Modul yang Dikerjakan

### 1. Department
![Department 1](images/department_1.png?raw=true)
![Department 2](images/department_2.png?raw=true)

### 2. Employee
![Employee 1](images/employee_1.png?raw=true)
![Employee 2](images/employee_2.png?raw=true)
![Employee 3](images/employee_3.png?raw=true)

### 3. Attendance
![Attendance 1](images/attendance_1.png?raw=true)
![Attendance 2](images/attendance_2.png?raw=true)
![Attendance 3](images/attendance_3.png?raw=true)
![Attendance 4](images/attendance_4.png?raw=true)

## Cara Menjalankan Project

Note terlebih dahulu on How to Run:
- use PHP 7.3.24
- use Postgre 18
- Laravel 8^
- composer install
- php artisan generate:key
- Ganti password `DB_PASSWORD` di file .env menjadi password postgres pengguna (DB_USERNAME juga jika berbeda)
- Lebih baik `php artisan migrate` saja daripada harus restore menggunakan [HMR.sql](HRM.sql)

1. Pastikan PHP, Composer, dan PostgreSQL sudah terpasang.
2. Masuk ke folder project.
3. Install dependency Composer.
4. Siapkan file `.env`.
5. Atur koneksi database PostgreSQL.
6. Jalankan migration dan seeder.
7. Jalankan server Laravel.

Perintah yang dipakai:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Konfigurasi database yang dipakai di project ini sudah diarahkan ke PostgreSQL melalui `DB_CONNECTION=pgsql`.

## Hasil Pengecekan Terhadap Requirement Soal

Saya bandingkan implementasi project ini dengan isi file technical test. Hasilnya seperti berikut.

### Requirement utama

- PHP 7 dengan framework: terpenuhi. Project memakai Laravel 8 dan requirement di `composer.json` masih kompatibel dengan PHP 7.3 ke atas.
- PostgreSQL: terpenuhi. Konfigurasi `.env` memakai `pgsql`.
- CRUD Employee: terpenuhi.
- CRUD Department: terpenuhi.
- Attendance import dari Excel `.xlsx`: terpenuhi.
- Preview data attendance sebelum submit: terpenuhi.
- Read atau menampilkan daftar attendance: terpenuhi.

### Kesesuaian struktur tabel

Sebagian besar struktur tabel sudah sesuai dengan soal, terutama pada nama kolom utama, panjang data penting seperti `nik` dan `full_name`, penggunaan UUID sebagai primary key, serta relasi antar data.

Tetapi ada catatan yang perlu ditulis jujur:

- Pada dokumen soal, `dept_id` dan `employee_id` ditulis sebagai integer, sementara di implementasi ini keduanya memakai UUID.
- Alasan penyesuaian ini karena primary key tabel induknya juga UUID, jadi secara relasi database hasilnya tetap konsisten dan lebih rapi.
- Jadi, dari sisi fungsi aplikasi tidak ada masalah, tetapi kalau dibandingkan sangat ketat baris per baris dengan dokumen, tipe foreign key ini tidak persis sama.

### Catatan submission

Ada satu hal lagi yang perlu dicatat:

- Soal meminta repository berisi source code dan database file.
- Di project ini yang tersedia adalah migration dan seeder.
- File dump database terpisah seperti `.sql`. [HMR.sql](HRM.sql)
- Namun lebih baik gunakan migrate saja agar fresh new DB, dan cukup create DB HMR saja di postgre