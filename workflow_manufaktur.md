# Dokumentasi Lengkap Alur Kerja (Workflow) Sistem Informasi Manufaktur Tas

Dokumen ini memuat penjelasan komprehensif terkait setiap alur fungsional dalam aplikasi **Manufaktur Tas**, dari mulai autentikasi, pembagian hak akses (role), manajemen master data, siklus pengadaan & produksi, hingga pelaporan dan pencatatan riwayat (log aktivitas).

---

## 1. Alur Autentikasi & Keamanan (Semua Role)
Modul ini menangani keluar masuknya user dari sistem.
- **Login:** Pengguna memasukkan Username dan Password. Sistem memverifikasi dengan `password_verify()` terhadap *hash* di database. Berdasarkan kolom `role` di database, user akan diarahkan ke *Dashboard* yang sesuai dengan aksesnya.
- **Lupa Password:** Jika user lupa password, user dapat menggantinya langsung dengan memasukkan *Username*, lalu menginput *Password Baru* dan *Konfirmasi Password Baru*. Sistem mengecek kesesuaian username, memvalidasi input, membuat *hash* baru dengan `password_hash()`, dan memperbarui database.
- **Profil & Logout:** Setiap user bisa melihat dan mengubah nama/username/password mereka sendiri di menu Profil, lalu Logout untuk menghancurkan (destroy) sesi mereka.

---

## 2. Alur Pembagian Hak Akses (Role-Based Control)
Sistem ini membatasi menu dan fungsi berdasarkan 3 role utama.

### A. Role: Administrator (Kepala Pabrik / Owner)
*Administrator memiliki akses tak terbatas ke semua menu master data dan menjadi gerbang terakhir untuk persetujuan (validasi) produksi.*

1. **Dashboard Admin:** Menampilkan statistik agregat seluruh pabrik:
   - Total User terdaftar
   - Total Material yang ada
   - Total Produk terdaftar
   - Jumlah Pengajuan Produksi yang **Menunggu Validasi** (dilengkapi indikator warna dan badge).
   - Menampilkan *Log Aktivitas Terakhir* (10 log terbaru).
   - Menampilkan daftar *Material Paling Banyak Digunakan* untuk keperluan analisis tren.

2. **Kelola Users (Manajemen Karyawan):**
   - Admin bisa melihat daftar karyawan, menambah karyawan baru, dan menentukan jabatan mereka (Gudang atau Procurement).
   - Admin juga bisa mengedit profil karyawan atau menghapus akun mereka dari sistem.

3. **Kelola Master Data Material (Bahan Baku):**
   - Admin mendefinisikan *Kategori Material* (contoh: Kain, Aksesoris, Benang).
   - Admin mendaftarkan *Item Material Baru* lengkap dengan penentuan nama, kategori, satuan (meter, pcs, roll), dan **Batas Stok Minimum**. Batas minimum ini berfungsi sebagai *alarm* bagi tim Procurement.
   - Admin dapat **mengedit** data material (jika ada salah nama atau perubahan satuan) serta **menghapus** material yang tak dipakai lagi.

4. **Kelola Master Data Produk (Barang Jadi):**
   - Admin mendefinisikan *Kategori Produk* (contoh: Tas Ransel, Tas Selempang).
   - Admin mendaftarkan *Item Produk Baru* (contoh: "Tas Ransel Kanvas V1") tanpa mengisi stok, karena stok produk hanya akan bertambah dari hasil produksi yang sah.

5. **Bill of Materials (BOM) / Resep Produksi:**
   - Setelah produk dibuat, Admin masuk ke menu BOM untuk mendaftarkan resepnya.
   - Admin memilih Produk A, lalu memasukkan list material pembentuknya beserta kuantitas. 
   - *Contoh: BOM "Tas Selempang" = 1 Meter Kain Kanvas + 1 Pcs Resleting + 2 Meter Tali Webbing.*

6. **Validasi Produksi (Quality Control & Approval):**
   - Setelah tim Gudang selesai melakukan produksi fisik, data akan masuk ke halaman ini dengan status **Proses**.
   - Admin melakukan inspeksi fisik barang di lapangan.
   - **Tindakan (Setujui):** Jika kualitasnya lulus standar, Admin menekan "Setujui". Status berubah menjadi `selesai`. **Pada titik inilah stok Barang Jadi (Produk) bertambah.**
   - **Tindakan (Tolak):** Jika cacat atau salah, Admin menekan "Tolak". Status menjadi `gagal`. **Bahan baku yang sudah dipotong dari sistem dikembalikan sepenuhnya (refund) ke database Material.**

7. **Laporan & Analitik:**
   - Admin bisa mencetak rekap data stok material yang menipis (untuk budgeting pembelian selanjutnya).
   - Admin bisa mencetak rekapan produksi berdasarkan periode waktu (Harian, Bulanan, Tahunan) untuk evaluasi performa pabrik.

---

### B. Role: Procurement (Divisi Pengadaan / Pembelian)
*Procurement bertanggung jawab sebagai pemasok (supplier internal) agar rantai produksi tidak berhenti.*

1. **Dashboard Procurement:**
   - Memfokuskan tampilan pada indikator **"Material Menipis"** (stok < batas minimum yang di-set admin).
   - Menampilkan riwayat transaksi (Tambah Stok) yang pernah dilakukan oleh user tersebut.
   - Tabel khusus yang menampilkan material-material *kritis* yang butuh dibeli segera.

2. **Monitoring & Tambah Stok Material:**
   - Procurement bisa melihat daftar Material, namun **tidak bisa mengubah nama/satuannya** (itu tugas Admin).
   - Procurement hanya memiliki 1 tombol aksi: **Tambah Stok**.
   - Ketika bahan baku datang dari supplier, Procurement memilih material, memasukkan angka (contoh: masuk 100 meter kain kanvas), menambah catatan/keterangan nota, dan menyimpan. **Stok material di sistem otomatis bertambah.**

---

### C. Role: Gudang (Divisi Produksi / Eksekutor)
*Gudang adalah pembuat tas. Mereka menggunakan resep (BOM) dari Admin dan menghabiskan bahan baku dari Procurement.*

1. **Dashboard Gudang:**
   - Menampilkan statistik Total Produk yang sudah terdaftar.
   - Menampilkan riwayat pengajuan produksi yang mereka buat sendiri.
   - Menampilkan total stok produk yang tersedia di gudang penyimpanan.

2. **Proses Produksi Baru:**
   - Gudang masuk ke menu Produksi -> **Proses Produksi Baru**.
   - Memilih produk apa yang ingin dibuat dari *dropdown* (contoh: Memilih "Tas Selempang").
   - Sistem akan langsung menampilkan *"Kebutuhan Material Per Unit (BOM)"* berdasarkan setelan Admin.
   - Sistem secara otomatis menghitung kalkulasi *real-time*: **Berapa maksimal jumlah tas yang bisa dibuat dengan ketersediaan stok bahan baku di gudang saat ini?**
   - Jika stok kurang, tombol produksi akan dikunci (*disabled*) dan muncul peringatan merah *"Stok material tidak mencukupi"*.
   - Jika stok cukup, Gudang memasukkan jumlah (misal: 10 unit) dan menekan Submit.
   - **Tindakan Sistem saat Submit:** 
     1. Status laporan diset ke `Proses`.
     2. Stok material pembentuk (contoh: 10m kain, 10 resleting) langsung **dipotong/dikurangi** dari sistem agar material tersebut *ter-lock* dan tidak bisa dipakai oleh shift/produksi lain.
     3. Stok Barang Jadi (Produk) **belum ditambah** (menunggu acc Admin).

3. **Riwayat & Detail Produksi:**
   - Gudang dapat memantau status pengajuannya, apakah masih "Proses", sudah "Selesai" (Di-acc Admin), atau "Gagal" (Ditolak Admin).

---

## 3. Siklus Hidup Manufaktur (End-to-End Lifecycle)

Agar lebih terbayang, ini adalah kronologi perjalanan data 1 buah tas dalam aplikasi ini dari awal sampai akhir:

1. **Admin** membuat "Kain Katun" (Minimum: 20 Meter).
2. **Admin** membuat "Tas Tote Bag".
3. **Admin** memasang BOM "Tas Tote Bag": butuh 2 Meter "Kain Katun".
4. **Procurement** melihat Kain Katun stoknya masih 0 (Berstatus Menipis).
5. **Procurement** membeli 50 Meter Kain Katun dan klik *Tambah Stok* di sistem. (Stok Kain Katun = 50 Meter).
6. **Gudang** disuruh membuat 10 unit "Tas Tote Bag".
7. **Gudang** masuk menu produksi, pilih "Tas Tote Bag", ketik jumlah 10.
8. Sistem mengecek: 10 unit x 2 meter = 20 meter Kain. Karena stok ada 50, maka diizinkan.
9. **Gudang** menekan *Proses*. 
   - **Update Database A:** Stok Kain Katun berkurang 20 meter menjadi tersisa 30 meter.
   - **Update Database B:** Muncul record produksi status `Proses`.
10. Tas selesai dijahit secara fisik. Karyawan Gudang menyerahkan ke Admin.
11. **Admin** login, masuk menu Validasi, melihat ada 10 tas Tote Bag status `Proses`.
12. **Admin** menyetujuinya (klik *Setujui*).
   - **Update Database C:** Status berubah jadi `Selesai`.
   - **Update Database D:** Stok produk "Tas Tote Bag" bertambah 10 unit (Siap dipasarkan!).
13. Semua alur dari nomor 1-12 tercatat di tabel **Log Aktivitas** yang mencatat *"Siapa melakukan Apa pada Waktu Kapan"*.

---

## 4. Keamanan & Riwayat Log Sistem (Audit Trail)
Setiap tindakan krusial tidak pernah hilang begitu saja. Terdapat arsitektur tabel khusus bernama `log_aktivitas`.
Setiap kali ada user yang:
- Login/Logout
- Menambah, mengedit, menghapus Master Data
- Menambah Stok Material
- Melakukan Produksi
- Melakukan Validasi (Setuju/Tolak)

Maka fungsi `logAktivitas($pdo, $user_id, $aksi, $tabel, $referensi_id, $detail)` dipanggil di *background*. Fungsi ini memastikan pabrik memiliki rekam jejak (*Audit Trail*) yang jelas guna mencegah kecurangan (fraud), sehingga Manajer bisa tahu jam berapa tepatnya seseorang merubah data di dalam sistem.
