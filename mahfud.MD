## 1. GAMBARAN UMUM PROYEK

Sebuah sistem informasi manajemen manufaktur tas berbasis web yang melacak alur barang dari **bahan mentah (raw materials)** hingga **barang jadi (finished goods)**. Dibangun dengan **PHP Native** (tanpa framework) dan **MySQL**, dengan penekanan pada desain database relasional yang kuat karena ini dinilai untuk mata kuliah **Basis Data II**.

---

## 2. DESAIN DATABASE (ERD & RELASI)

Ini adalah bagian terpenting. Berikut rancangan tabelnya:

### Tabel-Tabel

**a. `users`** — Menyimpan data semua pengguna.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `nama` | VARCHAR(100) | Nama lengkap |
| `username` | VARCHAR(50), UNIQUE | Untuk login |
| `password` | VARCHAR(255) | Hash password (password_hash) |
| `role` | ENUM('administrator','procurement','gudang') | Hak akses |
| `created_at` | TIMESTAMP | Waktu registrasi |

**b. `kategori_material`** — Kategori bahan mentah (misal: kain, resleting, benang, dll).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `nama_kategori` | VARCHAR(100) | Nama kategori |

**c. `materials`** — Master data bahan mentah.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `kategori_id` | INT, FK → kategori_material(id) | Relasi ke kategori |
| `nama_material` | VARCHAR(150) | Nama bahan (misal: Kain Cordura 1000D) |
| `satuan` | VARCHAR(30) | meter, pcs, roll, kg |
| `stok` | DECIMAL(10,2) | Stok saat ini |
| `stok_minimum` | DECIMAL(10,2) | Batas minimum stok |
| `created_at` | TIMESTAMP | |

**d. `products`** — Master data barang jadi (tas).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `nama_produk` | VARCHAR(150) | Nama tas |
| `deskripsi` | TEXT | Deskripsi produk |
| `stok` | INT | Stok barang jadi |
| `created_at` | TIMESTAMP | |

**e. `bill_of_materials` (BOM)** — Resep: bahan apa saja yang dibutuhkan untuk 1 unit produk.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `product_id` | INT, FK → products(id) | Produk yang dihasilkan |
| `material_id` | INT, FK → materials(id) | Bahan yang dibutuhkan |
| `jumlah_dibutuhkan` | DECIMAL(10,2) | Qty bahan per 1 unit produk |

**f. `material_masuk`** — Log bahan mentah masuk (oleh Procurement).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `material_id` | INT, FK → materials(id) | Bahan yang ditambah |
| `user_id` | INT, FK → users(id) | Siapa yang menambah |
| `jumlah` | DECIMAL(10,2) | Qty masuk |
| `keterangan` | TEXT | Catatan/supplier |
| `tanggal` | TIMESTAMP | Waktu transaksi |

**g. `produksi`** — Log produksi barang jadi (oleh Gudang).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `product_id` | INT, FK → products(id) | Produk yang diproduksi |
| `user_id` | INT, FK → users(id) | Siapa yang memproduksi |
| `jumlah_produksi` | INT | Berapa unit diproduksi |
| `status` | ENUM('proses','selesai','gagal') | Status produksi |
| `tanggal` | TIMESTAMP | Waktu produksi |

**h. `produksi_detail`** — Detail bahan yang terpakai per produksi.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `produksi_id` | INT, FK → produksi(id) | Relasi ke header produksi |
| `material_id` | INT, FK → materials(id) | Bahan yang dipakai |
| `jumlah_terpakai` | DECIMAL(10,2) | Qty bahan terpakai |

**i. `log_aktivitas`** — Audit trail semua aktivitas.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT, PK, AI | Primary Key |
| `user_id` | INT, FK → users(id) | Pelaku |
| `aksi` | VARCHAR(50) | 'material_masuk', 'produksi', 'login', dll |
| `tabel_referensi` | VARCHAR(50) | Nama tabel terkait |
| `referensi_id` | INT | ID record terkait |
| `detail` | TEXT | Deskripsi aksi |
| `tanggal` | TIMESTAMP | |

### Relasi Antar Tabel (ERD Summary)

```
users (1) ──── (M) material_masuk (M) ──── (1) materials
users (1) ──── (M) produksi (M) ──── (1) products
users (1) ──── (M) log_aktivitas

kategori_material (1) ──── (M) materials

products (1) ──── (M) bill_of_materials (M) ──── (1) materials
produksi (1) ──── (M) produksi_detail (M) ──── (1) materials
```

---

## 3. HAK AKSES PER ROLE

| Fitur | Administrator | Procurement | Gudang |
|---|---|---|---|
| Dashboard & Statistik | ✅ Semua data | ✅ Data material | ✅ Data produksi |
| Kelola Users | ✅ CRUD | ❌ | ❌ |
| Lihat Materials | ✅ | ✅ | ✅ (read only) |
| Tambah Stok Material | ❌ | ✅ | ❌ |
| Lihat Products | ✅ | ✅ (read only) | ✅ |
| Kelola BOM | ✅ | ❌ | ❌ |
| Proses Produksi | ❌ | ❌ | ✅ |
| Log Aktivitas | ✅ Semua | ✅ Milik sendiri | ✅ Milik sendiri |
| Laporan Aggregate | ✅ | ❌ | ❌ |

---

## 4. STRUKTUR FOLDER PROYEK

```
manufaktur-tas/
├── config/
│   └── database.php          -- Koneksi PDO ke MySQL
├── includes/
│   ├── auth.php              -- Session, cek login, cek role
│   ├── header.php            -- HTML head + navbar
│   └── footer.php            -- Footer + closing tags
├── pages/
│   ├── dashboard.php         -- Halaman utama per role
│   ├── login.php
│   ├── logout.php
│   ├── users/                -- CRUD user (admin only)
│   ├── materials/            -- List, tambah stok
│   ├── products/             -- List, CRUD produk
│   ├── bom/                  -- Bill of Materials
│   ├── produksi/             -- Proses produksi (gudang)
│   ├── log/                  -- Log aktivitas
│   └── laporan/              -- Halaman laporan aggregate
├── assets/
│   ├── css/
│   └── js/
└── index.php                 -- Redirect ke login/dashboard
```

---

## 5. ALUR BISNIS SISTEM

**Alur lengkap dari mentah ke jadi:**

1. **Administrator** membuat master data material, produk, dan BOM (resep bahan per produk)
2. **Procurement** login → menambahkan stok bahan mentah → stok `materials` bertambah → tercatat di `material_masuk` dan `log_aktivitas`
3. **Gudang** login → pilih produk yang ingin diproduksi → sistem cek BOM dan stok bahan → jika cukup, proses produksi dimulai → stok `materials` berkurang, stok `products` bertambah → tercatat di `produksi`, `produksi_detail`, dan `log_aktivitas`
4. **Administrator** melihat laporan aggregate: material terbanyak dipakai, produk terlaris diproduksi, user paling aktif, dan material yang stoknya menipis

---

## 6. TIPS IMPLEMENTASI

Gunakan **PDO** dengan prepared statements untuk semua query agar aman dari SQL injection. Untuk autentikasi, gunakan `password_hash()` dan `password_verify()`. Middleware pengecekan role cukup dibuat sebagai fungsi di `auth.php` yang dipanggil di awal setiap halaman:

```php
// includes/auth.php
function cekRole($role_diizinkan) {
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        header('Location: /pages/dashboard.php');
        exit;
    }
}
// Pemanggilan di halaman:
cekRole(['administrator', 'procurement']);
```

---