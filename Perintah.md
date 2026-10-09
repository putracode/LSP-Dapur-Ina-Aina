BACA DULU SPESIFIKASI PROGRAM SpesifikasiProgram_Saputra_51423362_Dapur Ina Aina.pdf
# MASTER PROMPT: PENGEMBANGAN SISTEM INFORMASI POINT OF SALE (POS) & RESTORAN "DAPUR INA AINA"

## 1. IDENTITAS & OVERVIEW SISTEM
- **Nama Aplikasi**: Sistem Informasi POS dan Manajemen Restoran "Dapur Ina Aina"
- **Tech Stack**: Laravel 12/13, PHP 8.3+, MySQL, Blade Template Engine, Bootstrap 5.3+, AdminLTE v4.
- **Metodologi Pengembangan**: SDLC Waterfall.
- **Tujuan Sistem**: Mempercepat proses transaksi di meja kasir, manajemen stok bahan/menu real-time, pencatatan otomatis, serta rekapitulasi laporan berkala (mingguan/bulanan) untuk manajemen internal restoran.

---

## 2. ATURAN PENGGUNAAN TEMPLATE (ADMINLTE v4)
1. **Frontend Admin & Kasir**:
   - Seluruh antarmuka admin menggunakan layout terstruktur dari template AdminLTE v4 yang sudah tersedia di folder `public/adminlte/`.
   - Buat modular layout Blade (`resources/views/layouts/admin.blade.php`) yang memisahkan komponen `navbar`, `sidebar`, `footer`, dan `@yield('content')`.
---

## 3. PERAN PENGGUNA & HAK AKSES (RBAC)
Sistem memiliki 2 aktor utama dengan middleware otentikasi ketat:

### A. Role: Administrator (Admin)
- **Login / Logout**: Menggunakan kredensial `username` dan `password` (hashed).
- **Dashboard Admin**: Ringkasan statistik (total menu, stok menipis, omset hari ini/minggu ini/bulan ini, total transaksi).
- **Manajemen Kategori Menu**:
  - CRUD Kategori (Kategori default: `Makanan Utama`, `Appetizer`, `Minuman`).
- **Manajemen Data Menu & Stok**:
  - CRUD Menu (Field: Kategori, Nama Menu, Harga, Kuantitas Stok, Upload Foto Menu).
  - Monitoring stok menu secara real-time dan notifikasi stok kritis/habis.
- **Laporan Penjualan (Sales Report)**:
  - Filter laporan berkala: Harian, Mingguan, Bulanan, dan Custom Periode (Date Range).
  - Tampilan rekap: Total transaksi, item terlaris, total pendapatan tunai vs non-tunai.
  - Fitur Ekspor / Cetak Laporan (Print PDF / Excel-friendly).
- **Manajemen Pengguna (User Management)**:
  - CRUD akun pengguna (Tambah, ubah username/password/role, nonaktifkan/hapus akun) untuk aktor Admin dan Kasir.

### B. Role: Kasir
- **Login / Logout**: Sesi login khusus kasir.
- **Antarmuka POS (Point of Sale)**:
  - Tampilan grid/list menu yang dikelompokkan berdasarkan 3 tab kategori: *Makanan Utama*, *Appetizer*, *Minuman*.
  - Indikator stok real-time (menu otomatis disable jika stok = 0).
  - Keranjang Belanja (Cart/Billing): Tambah item, ubah quantity, hitung subtotal otomatis, dan total tagihan instan.
- **Pemrosesan Pembayaran**:
  - Pilihan metode pembayaran: **Tunai (Cash)** dan **Non-Tunai (QRIS / Transfer / Kartu)**.
  - Perhitungan uang bayar & kembalian untuk metode Tunai.
- **Konfirmasi Lunas & Pengurangan Stok Otomatis**:
  - Saat transaksi lunas (`status_pembayaran = 'lunas'`), sistem memotong stok `stok` di tabel menu secara atomik (`DB::transaction`).
- **Pencetakan Billing / Struk**:
  - Tampilan print receipt siap cetak (ukuran kertas thermal 58mm/80mm atau standar) yang mencakup: Nama Restoran (Dapur Ina Aina), Nomor Nota/Transaksi, Tanggal/Waktu, Kasir, Rincian Pesanan (Qty, Nama Item, Harga, Subtotal), Total, Metode Bayar, Jumlah Bayar, Kembalian, dan Ucapan Terima Kasih.

---

## 4. PERANCANGAN BASIS DATA (SKEMA DATABASE SESUAI SPESIFIKASI)

Implementasikan Migration & Model Eloquent sesuai nama tabel dan kolom spesifikasi:

### 1. Tabel `users`
- `id_user` : INT(11) AUTO_INCREMENT PRIMARY KEY
- `nama` : VARCHAR(100) NOT NULL
- `username` : VARCHAR(50) NOT NULL UNIQUE
- `password` : VARCHAR(255) NOT NULL
- `role` : ENUM('Admin', 'Kasir') NOT NULL
- `created_at` & `updated_at` : TIMESTAMP

### 2. Tabel `kategori`
- `id_kategori` : INT(11) AUTO_INCREMENT PRIMARY KEY
- `nama_kategori` : VARCHAR(50) NOT NULL (`Makanan Utama`, `Appetizer`, `Minuman`)
- `created_at` & `updated_at` : TIMESTAMP

### 3. Tabel `menu`
- `id_menu` : INT(11) AUTO_INCREMENT PRIMARY KEY
- `id_kategori` : INT(11) NOT NULL (Foreign Key -> `kategori.id_kategori` on cascade)
- `nama_menu` : VARCHAR(100) NOT NULL
- `harga` : DOUBLE NOT NULL
- `stok` : INT(11) NOT NULL DEFAULT 0
- `foto` : VARCHAR(255) NULLABLE (Path asset upload)
- `created_at` & `updated_at` : TIMESTAMP

### 4. Tabel `transaksi`
- `id_transaksi` : INT(11) AUTO_INCREMENT PRIMARY KEY
- `id_user` : INT(11) NOT NULL (Foreign Key -> `users.id_user`)
- `total_tagihan` : DOUBLE NOT NULL
- `metode_pembayaran` : VARCHAR(50) NOT NULL ('Tunai', 'Non-Tunai')
- `status_pembayaran` : VARCHAR(50) NOT NULL ('lunas', 'batal')
- `created_at` & `updated_at` : TIMESTAMP

### 5. Tabel `detail_transaksi`
- `id_detail` : INT(11) AUTO_INCREMENT PRIMARY KEY
- `id_transaksi` : INT(11) NOT NULL (Foreign Key -> `transaksi.id_transaksi` on cascade)
- `id_menu` : INT(11) NOT NULL (Foreign Key -> `menu.id_menu`)
- `kuantitas` : INT(11) NOT NULL
- `subtotal` : DOUBLE / INT(11) NOT NULL
- `created_at` & `updated_at` : TIMESTAMP

---

## 5. SEEDER & DATA AWAL (DEFAULT SETUP)
Buat Seeder untuk mempermudah pengujian:
1. **User Admin**: `username: admin`, `password: password123`, `role: Admin`, `nama: Administrator`
2. **User Kasir**: `username: kasir`, `password: password123`, `role: Kasir`, `nama: Kasir Dapur Ina Aina`
3. **Kategori Menu**: `Makanan Utama`, `Appetizer`, `Minuman`
4. **Sample Menu**:
   - Makanan Utama: Nasi Goreng Spesial, Ayam Bakar Madu, Bebek Goreng Kremes.
   - Appetizer: Tempe Mendoan, Tahu Bakso, Pangsit Goreng.
   - Minuman: Es Teh Manis, Es Jeruk, Jus Alpukat.

---

## 6. ARSITEKTUR KODE & BEST PRACTICES LARAVEL
- **Routing**: Pisahkan route auth, route admin (`/admin/*` dengan middleware check role Admin), dan route kasir (`/kasir/*` atau `/pos/*` dengan middleware check role Kasir).
- **Form Request Validation**: Validasi setiap input menu, stok, transaksi, dan user.
- **Keamanan Transaksi**: Gunakan `DB::beginTransaction()` dan `DB::commit()` saat memproses checkout pesanan dan pengurangan stok. Jika stok tidak mencukupi, lemparkan Exception dan rollback.
- **Flash Message**: Berikan feedback UI (Toast / Alert Bootstrap 5) untuk setiap aksi (sukses menyimpan, gagal, stok habis).
