# PCC-110 — Sistem Monitoring Response Time Layanan 110

Aplikasi web native PHP + MySQL/MariaDB untuk Laragon.

## Basis rancangan
- Proposal: Sistem Monitoring Response Time Layanan 110 Polrestabes Semarang.
- HTML acuan: `sistem-monitoring-110-polrestabes-semarang.html`.
- Stack: Native PHP, PDO, MySQL/MariaDB, HTML5, CSS3, Vanilla JS.
- Chart: Chart.js CDN.
- PDF: Dompdf via Composer (opsional untuk export PDF; CSV tetap native).
- WhatsApp: `wa.me` deep-link, tanpa WhatsApp Business API.

## Pembagian Job Desc
- UDINUS 1: `operator/` + autentikasi operator + input laporan + riwayat.
- UDINUS 2: `pamapta/` + timestamp Berangkat/Tiba/Selesai + foto + tanda tangan.
- UDINUS 3: `includes/whatsapp.php`, token unik, tracking W4, fallback copy.
- UDINUS 4: `pimpinan/` + `api/dashboard.php` + Chart.js + SLA + polling.
- UDINUS 5: `database/`, export CSV/PDF, audit log, testing, dokumentasi.

## Struktur
```text
pcc-110/
├── api/
│   ├── dashboard.php
│   └── chart.php
├── assets/
│   ├── css/app.css
│   └── js/app.js
├── config/
│   └── config.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   ├── MANUAL_BOOK.md
│   └── UAT_CHECKLIST.md
├── exports/
├── includes/
│   ├── auth.php
│   ├── db.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   └── whatsapp.php
├── operator/
│   ├── dashboard.php
│   ├── laporan-baru.php
│   └── logout.php
├── pamapta/
│   └── detail.php
├── pimpinan/
│   └── dashboard.php
├── storage/uploads/
├── composer.json
├── index.php
└── login.php
```

## Instalasi Laragon

1. Salin folder `pcc-110` ke:
   `C:\laragon\www\pcc-110`

2. Pastikan Laragon menjalankan Apache dan MySQL/MariaDB.

3. Buka HeidiSQL/phpMyAdmin atau terminal Laragon.

4. Buat database:
```sql
CREATE DATABASE `pcc-110` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. Import:
   - `database/schema.sql`
   - `database/seed.sql`

   Jika memakai terminal:
```bat
cd C:\laragon\www\pcc-110
mysql -u root -p pcc-110 < database\schema.sql
mysql -u root -p pcc-110 < database\seed.sql
```
Jika root Laragon tidak memakai password, hilangkan `-p`.

6. Atur koneksi di `config/config.php`:
```php
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=pcc-110
DB_USER=root
DB_PASS=
```

7. Untuk URL:
   - `http://pcc-110.test` jika Auto Virtual Hosts Laragon aktif.
   - atau `http://localhost/pcc-110/`.

8. Login dummy untuk pengujian lokal:
   - Operator: `operator` / `password`
   - Pamapta: `pamapta` / `password`
   - Pimpinan: `pimpinan` / `password`

**Semua akun di atas adalah data dummy. Ganti password sebelum dipakai nyata.**

## Composer / PDF

Dompdf dipakai sesuai proposal. Jalankan dari terminal Laragon:
```bat
cd C:\laragon\www\pcc-110
composer install
```

Jika Composer belum ada:
```bat
composer require dompdf/dompdf
```

Tanpa Dompdf, seluruh fungsi utama tetap berjalan dan export CSV tersedia. Export PDF akan memberi pesan instalasi dependency.

## Alur uji end-to-end

1. Login Operator.
2. Buat laporan baru.
3. Sistem membuat tiket `TIKET-YYYYMMDD-XXXX`, nomor laporan, dan token.
4. Klik `Kirim WhatsApp` atau `Salin Pesan`.
5. Buka URL token di browser lain/HP.
6. Pamapta menekan `Berangkat`.
7. Pamapta menekan `Tiba di TKP`.
8. Isi hasil tindak lanjut.
9. Upload 1–5 foto dokumentasi.
10. Tanda tangan digital.
11. Tekan `Selesai`.
12. Buka Dashboard Pimpinan.
13. Dashboard melakukan polling data dan menghitung KPI.

## Formula
- Response Time Total = W7 - W1
- Waktu Respons Awal = W6 - W1
- Travel Time = W6 - W5
- Handling Time = W7 - W6

SLA visual mengikuti proposal:
- Hijau: < 5 menit
- Kuning: 5–10 menit
- Merah: > 10 menit

## Catatan implementasi
- `waktu_laporan` dapat diisi operator untuk menyesuaikan jam telepon 110.
- `waktu_input` selalu dibuat server saat submit.
- `waktu_kirim` dibuat saat operator menekan tombol WhatsApp.
- `waktu_buka` dicatat sekali saat token pertama kali dibuka.
- Token Pamapta adalah 32-byte random token.
- Upload membatasi 5 file dan memvalidasi MIME/ukuran.
- Semua query database menggunakan PDO prepared statements.
- Password memakai `password_hash()` / `password_verify()`.
- Audit log mencatat aksi penting.
- Tidak ada nama anggota atau nama regu nyata di seed data.

## Dummy data
`database/seed.sql` hanya berisi:
- 3 akun role generik untuk testing.
- 1–2 contoh laporan generik tanpa nama anggota/regu nyata.
- Tidak ada data personel nyata.
