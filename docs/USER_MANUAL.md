# FreshMart Online - User Manual

## Panduan Penggunaan Aplikasi

### 1. Registrasi Akun

#### Sebagai Pembeli:
1. Buka halaman **Daftar** dari menu navigasi.
2. Isi data: Nama, Email, No. Telepon, Alamat.
3. Pilih role **Pembeli**.
4. Buat password minimal 6 karakter.
5. Klik **Daftar**. Akun langsung aktif.

#### Sebagai Penjual:
1. Buka halaman **Daftar**.
2. Isi data lengkap toko Anda.
3. Pilih role **Penjual**.
4. Klik **Daftar**.
5. **Tunggu verifikasi admin** sebelum bisa mulai berjualan.
6. Admin akan memverifikasi melalui menu **Kelola User > Verifikasi**.

---

### 2. Login

1. Buka halaman **Login**.
2. Masukkan email dan password.
3. Centang "Ingat saya" jika ingin browser mengingat email.
4. Klik **Masuk**.
5. Anda akan diarahkan ke dashboard sesuai role.

---

### 3. Fitur Pembeli (Buyer)

#### Mencari & Melihat Produk:
1. Dari **Beranda**, scroll untuk melihat produk terbaru.
2. Gunakan **search bar** di navbar untuk mencari produk.
3. Klik ikon kategori untuk filter berdasarkan kategori.
4. Klik **Detail** untuk melihat informasi lengkap produk.
5. Lihat **review & rating** dari pembeli lain.

#### Menambah ke Keranjang:
1. Di halaman detail produk, klik **Tambah ke Keranjang**.
2. Jumlah akan otomatis ditambahkan (qty 1).
3. Badge keranjang di navbar menunjukkan jumlah item.

#### Mengelola Keranjang:
1. Klik ikon **Keranjang** di navbar.
2. Gunakan tombol **+/-** untuk mengubah jumlah.
3. Klik **hapus** untuk menghapus item.
4. Sistem otomatis menghitung ongkir berdasarkan berat total.

#### Checkout:
1. Dari keranjang, klik **Checkout**.
2. Isi alamat pengiriman.
3. Pilih metode pembayaran (Transfer BCA/Mandiri/BNI, COD, E-Wallet).
4. Klik **Buat Pesanan**.
5. Stok produk otomatis berkurang (System Automation).

#### Pembayaran:
1. Setelah checkout, buka **Pesanan Saya**.
2. Klik **Bayar** pada pesanan dengan status "pending".
3. Transfer ke rekening yang ditampilkan.
4. Upload bukti transfer (screenshot/foto).
5. Tunggu verifikasi admin.

#### Tracking Pesanan:
1. Buka **Detail Pesanan**.
2. Lihat progress bar tracking (5 tahap).
3. Terima notifikasi setiap kali status berubah.

#### Review Produk:
1. Setelah pesanan diterima (delivered), buka halaman produk.
2. Isi rating bintang (1-5).
3. Tulis komentar review.
4. Opsional: upload foto review.
5. Klik **Kirim Review**.

---

### 4. Fitur Penjual (Seller)

#### Dashboard:
- Lihat statistik: total produk, pesanan, pendapatan.
- Pesanan terbaru ditampilkan di halaman utama.

#### Menambah Produk:
1. Klik **Kelola Produk > Tambah Produk**.
2. Isi: nama, kategori, harga, stok, berat, deskripsi.
3. Upload gambar produk (JPG/PNG).
4. Klik **Simpan Produk**.

#### Edit Produk:
1. Dari daftar produk, klik ikon **edit**.
2. Ubah data yang diperlukan.
3. Upload gambar baru jika perlu.
4. Klik **Update Produk**.

#### Menghapus Produk:
1. Dari daftar produk, klik ikon **hapus**.
2. Konfirmasi penghapusan.

#### Mengelola Pesanan:
1. Buka **Pesanan Masuk**.
2. Lihat daftar pesanan yang mengandung produk Anda.
3. Klik **Konfirmasi** untuk menyetujui pesanan yang sudah dibayar.
4. Klik **Kirim** setelah barang dikirim.
5. Notifikasi otomatis dikirim ke pembeli.

---

### 5. Fitur Admin

#### Dashboard:
- Statistik keseluruhan: pendapatan, pesanan, user.
- Distribusi user dan status pesanan.

#### Kelola User:
1. Buka **Kelola User**.
2. Filter: Semua / Penjual / Pembeli.
3. Klik **Verifikasi** untuk menyetujui penjual baru.
4. Klik **hapus** untuk menghapus user.

#### Kelola Kategori:
1. Buka **Kelola Kategori**.
2. **Tambah**: Isi nama, icon emoji, deskripsi.
3. **Edit**: Klik ikon edit, ubah data, simpan.
4. **Hapus**: Klik ikon hapus.

#### Kelola Pesanan:
1. Buka **Kelola Pesanan**.
2. **Verifikasi Pembayaran**: Lihat bukti transfer, klik Verifikasi/Tolak.
3. **Update Status**: Pilih status baru, klik Update.
4. Status otomatis berubah saat pembayaran diverifikasi.
5. Stok otomatis kembali saat pesanan dibatalkan.

#### Laporan:
- Penjualan bulanan.
- Top produk (paling laris).
- Top penjual (pendapatan tertinggi).
- Top pembeli (paling aktif).
- Penjualan per kategori.

#### Invoice:
1. Buka **Settings & Invoice**.
2. Klik **Cetak Invoice** pada pesanan.
3. Invoice akan tampil di tab baru, siap dicetak.

---

### 6. System Automation (Otomatis)

Fitur yang berjalan otomatis tanpa intervensi manual:

| Fitur | Keterangan |
|-------|-----------|
| Auto Update Stok | Stok berkurang saat checkout, kembali saat cancel |
| Auto Kalkulasi Ongkir | Rp 10.000/kg, minimal Rp 15.000 |
| Auto Notification | Notifikasi otomatis saat status berubah |
| Auto Update Status | Status order berubah saat bayar diverifikasi |
| Auto Generate Invoice | Invoice tersedia otomatis untuk setiap pesanan |

---

### 7. Troubleshooting

#### Halaman blank/putih:
- Cek konfigurasi database di `src/config/database.php`.
- Pastikan PHP 8.x terinstall.
- Aktifkan `display_errors` di php.ini untuk debug.

#### Login gagal:
- Pastikan password = `password` untuk akun demo.
- Pastikan data sudah diimport ke MySQL.

#### Gambar tidak muncul:
- Pastikan folder `src/uploads/` writable.
- Cek permission folder.

#### Cart tidak berfungsi:
- Pastikan session PHP aktif.
- Cek browser mengizinkan cookie.
