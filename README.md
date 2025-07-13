# 📬 Aplikasi Arsip Surat

Aplikasi Arsip Surat ini merupakan sistem manajemen arsip surat masuk dan surat keluar yang dapat diakses secara online melalui alamat [https://asephilmi.my.id/surat/](https://asephilmi.my.id/surat/). Sistem ini dibuat untuk membantu proses pencatatan, pelacakan, dan pengarsipan surat secara digital.

---

## 🔧 Fitur Utama

- 🔐 **Login Admin**
  - Sistem otentikasi untuk menjaga keamanan data
- 📥 **Manajemen Surat Masuk**
  - Input, edit, dan hapus data surat masuk
  - Filter berdasarkan tahun
- 📤 **Manajemen Surat Keluar**
  - Input, edit, dan hapus data surat keluar
  - Filter berdasarkan tahun
- 📊 **Dashboard Ringkas**
  - Statistik jumlah surat masuk dan keluar
- 📁 **Upload & Download File**
  - Setiap surat dapat dilampirkan file PDF atau gambar
- 📅 **Filter Data**
  - Berdasarkan tahun dan jenis surat
- 🖨️ **Cetak atau Unduh**
  - Tampilan siap cetak dan unduh file surat

---

## 🗂️ Struktur Folder
/surat/
├── css/ # Berkas CSS untuk styling
├── js/ # Berkas JavaScript pendukung
├── uploads/ # Folder penyimpanan file surat
├── surat_masuk.php # Halaman kelola surat masuk
├── surat_keluar.php # Halaman kelola surat keluar
├── index.php # Dashboard utama
├── login.php # Form login admin
├── config.php # Koneksi database & konfigurasi umum
├── logout.php # Logout user
└── ... (halaman lainnya)


---

## 🛠️ Teknologi yang Digunakan

- **PHP** – Untuk logika backend
- **MySQL/MariaDB** – Penyimpanan data surat
- **HTML5, CSS3, Bootstrap** – Tampilan antarmuka
- **JavaScript/jQuery** – Interaktivitas pengguna
- **FontAwesome** – Ikon-ikon modern

---

## 🔒 Keamanan

- Validasi input form
- Session-based authentication
- Proteksi direktori `uploads/` agar file tidak langsung diakses

---

## 🚀 Cara Instalasi (di Localhost)

1. Clone atau unduh proyek ini:
    ```bash
    git clone https://asephilmi.my.id/surat/
    ```
2. Import file database (`database.sql`) ke phpMyAdmin.
3. Edit file `config.php` untuk menyesuaikan:
    ```php
    $db = new mysqli("localhost", "root", "", "nama_database");
    ```
4. Jalankan di browser:
    ```
    http://localhost/surat/
    ```

---

## 👤 Login Admin

- **Username:** admin  
- **Password:** admin123 (atau sesuai data pada tabel `admin`)

> ⚠️ Harap ganti password default setelah instalasi untuk keamanan.

---

## 📝 Lisensi

Proyek ini bersifat open-source dan bebas digunakan untuk keperluan edukasi dan pengembangan sistem internal.

---

## 📩 Kontak

Jika ada pertanyaan, saran, atau ingin mengembangkan sistem ini, hubungi:

**Asep Hilmi**  
[https://asephilmi.my.id](https://asephilmi.my.id)


