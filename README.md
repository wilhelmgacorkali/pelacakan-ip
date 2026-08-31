# 🌐 GeoTrack Pro - Sistem Pelacakan IP & Intelijen Nomor Telepon (Laravel)

Aplikasi berbasis **Laravel** untuk melacak geolokasi alamat IP secara interaktif dengan peta (Leaflet.js + OpenStreetMap) serta analisis operator dan validasi nomor telepon (Indonesia & Internasional), dilengkapi sistem pencatatan riwayat (audit log) dan ekspor data CSV.

Proyek ini dirancang secara khusus untuk memenuhi standar tugas kelulusan / skripsi bidang Rekayasa Perangkat Lunak, Jaringan, dan Keamanan Informasi.

---

## 🚀 Fitur-Fitur Utama

### 1. Pelacak Geolokasi IP (IP Tracker)
- **One-Click My IP**: Tombol otomatis untuk mendeteksi alamat IP publik pengunjung saat ini.
- **Peta Interaktif Visual**: Integrasi *Leaflet.js & OpenStreetMap* yang menandai titik koordinat (Latitude & Longitude) secara interaktif.
- **Informasi Lengkap**:
  - Negara (dengan Bendera Emoji), Provinsi, Kota, Kode Pos.
  - Internet Service Provider (ISP) / Organisasi pemilik IP.
  - Autonomous System Number (ASN) & BGP Routing.
  - Zona Waktu & Waktu Lokal.
  - Deteksi Keamanan (Status Proxy / VPN / Hosting Datacenter).

### 2. Intelijen Nomor Telepon (Phone Intelligence)
- **Deteksi Operator Indonesia Otomatis**:
  - **Telkomsel** (Kartu Halo, simPATI, KARTU As, By.U)
  - **Indosat Ooredoo Hutchison** (IM3, Mentari, Matrix)
  - **XL Axiata** (XL Prabayar, Prioritas, Live.On)
  - **AXIS**
  - **Tri (3)**
  - **Smartfren** (4G LTE / eSIM)
- **Kode Area PSTN / Telepon Kabel**: Memetakan kode area telepon rumah/kantor di seluruh kota di Indonesia (021 Jabodetabek, 022 Bandung, 031 Surabaya, dll).
- **Standarisasi Internasional**:
  - Format E.164 Internasional (+62...)
  - Format Lokal Nasional (0812-xxxx)
  - Format URI Click-to-Call (`tel:+62...`)
- **Validasi Panjang Digit & Standar ITU-T**.

### 3. Riwayat & Log Audit (History & Export)
- Menyimpan setiap aktivitas pelacakan ke database.
- Filter pencarian berdasarkan tipe (IP / Nomor Telepon).
- Fitur **Export Data ke CSV** (Dilengkapi UTF-8 BOM agar rapi saat dibuka di Microsoft Excel).

### 4. Halaman Metodologi & Landasan Teori
- Halaman dokumentasi teknis yang menjelaskan teori *BGP routing, RIR (APNIC), MSISDN, regulasi UU PDP No. 27/2022* untuk mempermudah saat diuji dosen.

---

## 💻 Panduan Menjalankan dengan Laragon

1. **Buka Laragon** di komputer Anda.
2. Klik tombol **Start All** (untuk mengaktifkan Apache/Nginx dan MySQL).
3. Klik tombol **Terminal** pada Laragon (Terminal bawaan Laragon otomatis sudah terkonfigurasi PHP dan Composer).
4. Masuk ke folder proyek ini di terminal:
   ```bash
   cd C:\IP
   ```
5. Install dependensi (jika baru pertama kali):
   ```bash
   composer install
   ```
6. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```
7. Jalankan web server Laravel:
   ```bash
   php artisan serve
   ```
8. Buka browser dan akses:
   ```
   http://localhost:8000
   ```

---

## 🎓 Poin Penting untuk Presentasi / Sidang Tugas Akhir

Jika dosen penguji menanyakan mengenai **tingkat akurasi sistem**:

1. **Tingkat Akurasi IP**:
   - Akurasi IP Geolocation adalah pada tingkat **Kota, Provinsi, dan ISP** (sekitar 85%–95% akurat pada level regional).
   - Mengapa tidak bisa sampai alamat rumah/kamar? Karena ISP mengalokasikan IP dinamis menggunakan DHCP/CGNAT. Mengetahui hal ini membuktikan bahwa Anda memahami konsep jaringan komputer secara mendalam.

2. **Tingkat Akurasi Nomor Telepon**:
   - Akurasi deteksi **Operator dan Wilayah Registrasi adalah 100% akurat** berdasarkan blok nomor prefix MSISDN dan regulasi Kominfo / ITU-T E.164.
   - Posisi GPS live seseorang tidak dapat dilacak sembarangan tanpa izin perangkat / jalur legal penegak hukum ke jaringan inti SS7 operator, demi mematuhi **UU Perlindungan Data Pribadi (UU PDP No. 27/2022)**.

## Phone Intelligence API (GeoTrack Pro)

Versi ini mempertahankan UI dan fitur lama, tetapi menambahkan provider Phone Intelligence server-side.

### Provider default

`IPQualityScore (IPQS)` dipakai sebagai provider utama jika `PHONE_LOOKUP_API_KEY` diisi. API ini dapat mengembalikan validitas nomor, carrier, line type, country, region, city, timezone, dan sinyal status nomor. Data lokasi nomor tetap merupakan **asosiasi wilayah**, bukan GPS perangkat.

### Konfigurasi `.env`

```env
PHONE_LOOKUP_PROVIDER=ipqs
PHONE_LOOKUP_API_KEY=ISI_API_KEY_ANDA
PHONE_LOOKUP_API_URL=https://www.ipqualityscore.com/api/json/phone
PHONE_LOOKUP_TIMEOUT=8
PHONE_LOOKUP_STRICTNESS=0
GEOCODER_USER_AGENT="GeoTrack-Pro/1.0 contact-admin@example.com"
```

API key dipakai **server-side** dan tidak dikirim ke JavaScript/frontend.

Jika `PHONE_LOOKUP_API_KEY` kosong atau provider gagal, aplikasi otomatis menggunakan database prefix/HLR lama sebagai fallback sehingga UI tetap berjalan.

### Catatan lokasi

- HLR/prefix, city/region dari provider, dan koordinat hasil geocoding **bukan GPS aktual**.
- GPS aktual hanya dapat diperoleh dari perangkat yang bersangkutan melalui mekanisme lokasi berbasis izin pengguna.
- Tombol WhatsApp pada aplikasi hanya membuka chat / menyusun permintaan berbagi lokasi; aplikasi tidak mengambil GPS pemilik nomor secara diam-diam.
