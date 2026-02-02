# Absensi OCR System

Sistem Presensi Otomatis berbasis AI yang menggabungkan teknologi **Optical Character Recognition (OCR)** dan **Face Recognition** untuk verifikasi kehadiran karyawan dan anak magang yang lebih akurat dan praktis.

## 🚀 Fitur Utama

1.  **Check-In via OCR & Face Recognition**:
    - Pengguna hanya perlu mengambil satu foto yang memperlihatkan **Wajah** dan **Kartu Identitas (ID Card)** secara bersamaan.
    - Sistem secara otomatis mengekstraksi nama dari ID Card menggunakan OCR.
    - Sistem memverifikasi apakah wajah di foto cocok dengan data wajah master di database.
2.  **Check-Out via Face Recognition**:
    - Pengguna cukup mengambil foto selfie.
    - Sistem akan mencari kecocokan wajah di seluruh database karyawan/magang yang aktif untuk mencatat waktu pulang.
3.  **Manajemen Data (Filament Dashboard)**:
    - Kelola data Karyawan (Employee) dan Anak Magang (Intern).
    - Monitor riwayat kehadiran (Presence) dengan status validasi otomatis.

## 🛠️ Alur Kerja Teknis

Sistem ini terdiri dari dua komponen utama:

- **Backend Laravel**: Mengelola logika bisnis, database (MySQL), dan dashboard admin menggunakan Filament.
- **AI Service (Flask)**: Layanan terpisah (Python) yang menangani pemrosesan gambar berat menggunakan library `dlib` (Face Recognition) dan `EasyOCR/Tesseract` (OCR).

### Proses Absen Masuk:

1.  Frontend mengirim foto (Wajah + ID Card) ke Laravel.
2.  Laravel meneruskan foto ke Python Flask Service.
3.  Flask mengekstraksi teks dari ID Card dan menghitung _face encoding_.
4.  Laravel mencari User berdasarkan nama hasil OCR.
5.  Jika nama ditemukan, Laravel membandingkan _face encoding_ foto saat ini dengan foto master.
6.  Jika cocok (skor di atas ambang batas), data absen disimpan sebagai `valid`.

## 📦 Teknologi yang Digunakan

- **Framework**: Laravel 11.x
- **Admin Panel**: Filament PHP (TALL Stack)
- **Database**: MySQL
- **AI/ML Service**: Python Flask
- **Computer Vision**:
    - `dlib` / `face_recognition` (untuk perbandingan wajah)
    - `EasyOCR` (untuk ekstraksi teks ID Card)

## 📋 Prasyarat

- PHP 8.2+
- Composer
- Node.js & NPM
- Python 3.10+ (untuk AI Service)
- Tesseract OCR engine (terinstal di sistem)

---

_Dibuat untuk sistem manajemen kehadiran yang cerdas dan aman._
