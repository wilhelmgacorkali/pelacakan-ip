# GeoTrack Pro - Device Tracking

Modul ini menambahkan **Device Agent + Laravel Location API + live map** untuk perangkat yang sudah didaftarkan/dikuasai pengguna.

## Cara pakai

1. Jalankan migrasi:
   `php artisan migrate`
2. Buka `/devices`.
3. Isi nama device dan email atau nomor HP, lalu klik **Buat Device Agent**.
4. Buka URL Device Agent pada perangkat yang hendak didaftarkan.
5. Pada perangkat tersebut, klik **Aktifkan Lokasi Perangkat** dan izinkan lokasi pada browser.
6. Kembali ke `/devices`; peta akan memperbarui lokasi terbaru setiap 5 detik.

## Data yang disimpan

- Latitude / longitude GPS dari Geolocation API.
- Accuracy GPS.
- Timestamp.
- IP yang terlihat oleh server saat device mengirim update.
- User agent/platform.

**Catatan:** nomor HP/email adalah identifier perangkat, bukan sumber GPS. Modul ini tidak mengambil lokasi perangkat hanya dari nomor HP/email. GPS hanya diterima dari Device Agent pada perangkat yang telah didaftarkan dan memberikan izin lokasi.

## HTTPS

Untuk domain publik, browser umumnya mensyaratkan HTTPS agar Geolocation API dapat digunakan. `localhost` biasanya tetap diizinkan untuk development.

## IP perangkat

`ip_address` adalah IP sumber request yang terlihat oleh server. Pada jaringan seluler, IP dapat berupa IP gateway/NAT operator dan tidak selalu merupakan IP unik perangkat.
