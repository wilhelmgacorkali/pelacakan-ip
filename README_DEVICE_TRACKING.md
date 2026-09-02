# GeoTrack Pro - Device Tracking (Consent-Based)

Modul ini menambahkan **Device Agent + Laravel Location API + live map** untuk perangkat yang **sudah diberi izin eksplisit** oleh penerima link. Ini bukan alat pelacakan diam-diam — setiap link permintaan lokasi menampilkan jelas siapa pemintanya dan penerima selalu bisa menolak/menghentikan.

## Prinsip desain

1. **Identitas peminta selalu terlihat.** Halaman Device Agent menampilkan nama & foto orang yang meminta lokasi, bukan link generik/anonim.
2. **Izin eksplisit.** Lokasi baru dikirim setelah penerima menekan tombol "Aktifkan & Bagikan Lokasi Saya" dan mengizinkan akses lokasi di browser.
3. **Kontrol penuh di tangan penerima.** Tombol "Hentikan Berbagi" selalu tersedia di halaman yang sama; sekali ditekan, server menolak update lokasi berikutnya untuk token tersebut (lihat `sharing_enabled` di tabel `devices`).
4. **Tidak ada pelacakan berbasis nomor HP/email semata.** Nomor HP/email hanya dipakai sebagai *label kontak* perangkat, bukan sumber GPS.

## Cara pakai

1. Jalankan migrasi:
   `php artisan migrate`
2. Buka `/devices`.
3. Isi form "Daftarkan Device": nama perangkat, kontak, **serta identitas Anda sendiri** (nama & foto — ini yang akan ditampilkan ke penerima) dan alasan/tujuan permintaan.
4. Klik **Buat Link Permintaan Lokasi**, lalu kirim link tersebut ke orang yang bersangkutan (mis. lewat chat).
5. Saat penerima membuka link, mereka akan melihat identitas Anda dan penjelasan tujuan, lalu bisa menekan **Aktifkan & Bagikan Lokasi Saya** untuk mulai berbagi, atau mengabaikannya.
6. Kembali ke `/devices`; peta akan memperbarui lokasi terbaru setiap 5 detik selama penerima masih membagikan lokasi.

## Akurasi lokasi

- Saat diaktifkan, agent langsung mengambil satu pembacaan cepat (`getCurrentPosition`) supaya peta pengirim langsung terisi, lalu berpindah ke `watchPosition` untuk update berkelanjutan.
- `enableHighAccuracy: true` dan `maximumAge: 0` dipakai supaya browser mengutamakan GPS chip (bukan cache/IP Wi-Fi) — akurasi biasanya turun ke level meter di luar ruangan.
- Status akurasi (±meter) ditampilkan real-time di halaman agent, dengan label "sangat baik / baik / sedang / rendah" agar penerima tahu kualitas sinyal saat itu.

## Menghentikan berbagi

Penerima dapat menghentikan berbagi kapan saja dengan salah satu dari:
- Menekan tombol **Hentikan Berbagi** di halaman agent (memanggil `POST /api/device-agent/{token}/revoke`, menandai `sharing_enabled=false`).
- Menutup halaman / mencabut izin lokasi di browser.
- Setelah dihentikan, endpoint `location()` akan menolak (HTTP 403) update lokasi baru untuk token tersebut sampai diaktifkan ulang oleh pemilik data.

## Data yang disimpan

- Latitude / longitude GPS dari Geolocation API.
- Accuracy GPS.
- Timestamp.
- IP yang terlihat oleh server saat device mengirim update.
- User agent/platform.
- Identitas peminta (nama, foto, alasan) — disimpan di tabel `devices`.

## HTTPS & Ngrok

Browser mensyaratkan HTTPS agar Geolocation API dapat dipakai di domain publik (`localhost` biasanya tetap diizinkan untuk development). Untuk mengetes link di perangkat lain (HP penerima) tanpa deploy, gunakan tunnel ngrok bawaan:

```bash
php artisan tracker:tunnel
```

Perintah ini akan:
1. Menjalankan `php artisan serve` di port lokal (default 8000).
2. Menjalankan `ngrok http` mengarah ke port tersebut (otomatis pakai `NGROK_AUTHTOKEN`/`NGROK_DOMAIN` dari `.env` jika diisi).
3. Menampilkan URL publik ngrok (HTTPS) di terminal — buka `/devices` di URL tersebut, buat link, lalu kirim link dari domain ngrok tadi ke penerima.

Opsi:
- `--port=8080` untuk mengubah port lokal.
- `--sync-env` untuk otomatis menulis ulang `APP_URL` di `.env` dengan URL ngrok (berguna karena `route()` memakai `APP_URL` untuk membangun link agent).

Konfigurasi terkait ada di `config/ngrok.php` dan env `NGROK_BINARY`, `NGROK_AUTHTOKEN`, `NGROK_DOMAIN`. Install ngrok terlebih dahulu dari https://ngrok.com/download dan pastikan binary-nya ada di PATH.

## IP perangkat

`ip_address` adalah IP sumber request yang terlihat oleh server. Pada jaringan seluler, IP dapat berupa IP gateway/NAT operator dan tidak selalu merupakan IP unik perangkat.
