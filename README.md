# Sistem Informasi Manajemen Manufaktur Tas

Sistem informasi berbasis web untuk mengelola proses manufaktur tas, dari bahan mentah hingga barang jadi.

## 🚀 Fitur Utama

- **Multi-Role System**: Administrator, Procurement, dan Gudang
- **User Management**: CRUD users dengan role-based access control
- **Material Management**: Kelola master data material (Admin) dan restock bahan mentah (Procurement) dengan alert stok menipis
- **Bill of Materials (BOM)**: Resep produksi untuk setiap produk
- **Production Management & Validation**: Tim gudang dapat mengajukan produksi, memotong stok material secara real-time, lalu menunggu **Validasi/Approval** dari Administrator. Jika disetujui, stok produk jadi bertambah. Jika ditolak, material dikembalikan.
- **Activity Logging**: Audit trail semua aktivitas pengguna
- **Aggregate Reports**: Laporan statistik dan analisis (Admin only)
- **Profile Management**: Edit profil dan ubah password
- **Password Reset Internal**: Fitur lupa password dengan penggantian langsung di dalam sistem
- **Sidebar-Only Modern UI**: Desain modern menggunakan Poppins font, sky-blue theme, dan layout *sidebar-only* untuk pengalaman pengguna yang bersih dan fokus.

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge)

## 🔧 Instalasi

### 1. Clone atau Download Project

```bash
git clone https://github.com/Tomi-012/Manufaktur-Basis-Data-II.git
cd Manufaktur
```

### 2. Import Database

1. Buka phpMyAdmin atau MySQL client
2. Buat database baru: `manufaktur_tas`
3. Import file `database.sql`

### 3. Konfigurasi Database

Edit file `config/database.php` sesuai dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'manufaktur_tas');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/Manufaktur/
```

## 👥 Default User Accounts

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| procurement | admin123 | Procurement |
| gudang | admin123 | Gudang |

**⚠️ PENTING**: Ubah password default setelah login pertama kali!

## 🎯 Alur Bisnis Baru

### 1. Setup Awal (Administrator)
- Login sebagai admin.
- Daftarkan Master Material, Master Produk, dan resep Bill of Materials (BOM).
- Buat akun untuk staf Gudang dan Procurement.

### 2. Procurement Process
- Login sebagai procurement.
- Pantau stok material. Jika ada notifikasi "Menipis", beli ke supplier.
- Update/tambah stok material yang baru tiba ke dalam sistem.

### 3. Production Process (Gudang)
- Login sebagai gudang.
- Pilih produk yang akan diproduksi.
- Sistem memvalidasi apakah stok material cukup. Jika cukup, stok material dipotong, dan status produksi menjadi **Proses**.
- Pekerjaan fisik perakitan tas dilakukan.

### 4. Validation & Closing (Administrator)
- Admin login dan membuka menu **Validasi Produksi**.
- Admin mengecek kualitas tas fisik yang dibuat Gudang.
- Jika disetujui: Stok tas (produk jadi) di sistem bertambah, status menjadi **Selesai**.
- Jika ditolak: Bahan baku dikembalikan ke stok gudang otomatis, status menjadi **Gagal**.

## 🔐 Hak Akses per Role

### Administrator
✅ Dashboard Statistik Penuh
✅ CRUD Users  
✅ CRUD Master Material & Produk
✅ Kelola BOM  
✅ Validasi / Approval Produksi
✅ Laporan aggregate & Log aktivitas
 
### Procurement
✅ Dashboard Notifikasi Material  
✅ Lihat Materials  
✅ Tambah Stok Material  
✅ Log aktivitas sendiri  

### Gudang
✅ Dashboard Produksi
✅ Lihat Products & Resep
✅ Pengajuan Proses Produksi (memotong bahan baku)
✅ Log aktivitas sendiri  

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native (tanpa framework) dengan PDO
- **Database**: MySQL
- **Frontend**: Bootstrap 5.3, Custom CSS (Sidebar Only Layout), Poppins Font
- **Icons**: Bootstrap Icons
- **Security**: Password Hashing, Prepared Statements, Session Management

## 📄 Lisensi

MIT License. Lihat file [LICENSE](LICENSE) untuk detailnya.

## 👨‍💻 Developer

Dikembangkan dengan ❤️ untuk mata kuliah Basis Data II

---

**Selamat menggunakan Sistem Manufaktur Tas!** 🎒
