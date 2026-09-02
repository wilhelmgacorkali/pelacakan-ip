@extends('layouts.app')

@section('title', 'Permintaan Berbagi Lokasi - '.$device->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Identitas peminta lokasi — WAJIB tampil jelas sebelum penerima memberi izin --}}
    <div class="glass-panel p-6 sm:p-8 rounded-2xl glow-effect">
        <div class="flex items-center gap-4">
            @if($device->requester_photo_url)
                <img src="{{ $device->requester_photo_url }}" alt="Foto {{ $device->requester_name }}"
                     class="w-14 h-14 rounded-full object-cover border-2 border-indigo-500/60 flex-shrink-0"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?background=6366f1&color=fff&name={{ urlencode($device->requester_name ?? '?') }}';">
            @else
                <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name={{ urlencode($device->requester_name ?? '?') }}"
                     alt="Foto {{ $device->requester_name }}" class="w-14 h-14 rounded-full flex-shrink-0">
            @endif
            <div class="min-w-0">
                <p class="text-xs text-slate-400">Permintaan berbagi lokasi dari</p>
                <h1 class="text-xl font-bold text-white truncate">{{ $device->requester_name ?? 'Tidak diketahui' }}</h1>
                @if($device->purpose)
                    <p class="text-xs text-slate-400 mt-0.5">Alasan: {{ $device->purpose }}</p>
                @endif
            </div>
        </div>

        <div class="mt-5 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-sm text-indigo-100 leading-relaxed">
            <strong>{{ $device->requester_name ?? 'Seseorang' }}</strong> meminta agar lokasi perangkat ini
            (label: <em>{{ $device->name }}</em>) dibagikan kepada mereka secara live.
            Lokasi hanya dikirim <strong>setelah</strong> Anda menekan tombol di bawah dan mengizinkan akses lokasi di browser.
            Anda dapat menghentikannya kapan saja dengan tombol <strong>“Hentikan Berbagi”</strong>, menutup halaman ini, atau mencabut izin lokasi pada browser.
        </div>

        <div id="revokedNotice" class="hidden mt-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-sm text-rose-200">
            Berbagi lokasi untuk perangkat ini telah dihentikan.
        </div>

        <div id="status" class="mt-4 p-4 rounded-xl bg-slate-900/80 border border-slate-700 text-sm text-slate-300">
            Menunggu Anda mengaktifkan berbagi lokasi perangkat ini.
        </div>

        <div class="mt-5 flex flex-col sm:flex-row gap-2">
            <button id="startBtn" onclick="startAgent()" class="flex-1 px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white rounded-xl font-semibold text-sm">
                <i class="fa-solid fa-location-crosshairs mr-2"></i>Aktifkan &amp; Bagikan Lokasi Saya
            </button>
            <button id="stopBtn" onclick="stopAndRevoke()" disabled class="px-5 py-3 bg-rose-600/20 hover:bg-rose-600/30 disabled:opacity-40 disabled:cursor-not-allowed text-rose-300 rounded-xl font-semibold text-sm border border-rose-500/40">
                <i class="fa-solid fa-stop mr-2"></i>Hentikan Berbagi
            </button>
        </div>

        <p class="text-xs text-slate-500 mt-4 leading-relaxed">
            Halaman ini memakai Geolocation API bawaan browser Anda &mdash; bukan pihak ketiga tersembunyi.
            Data yang dikirim: koordinat GPS, tingkat akurasi, dan waktu. Anda selalu tahu kapan halaman ini aktif mengirim lokasi lewat status di atas.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let watchId = null;
let sharing = false;
let bestAccuracy = Infinity;
let lastSentAt = 0;

function setStatus(message, type = 'normal') {
    const el = document.getElementById('status');
    el.innerHTML = message;
    el.className = 'mt-4 p-4 rounded-xl border text-sm ' + (type === 'ok'
        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
        : type === 'error'
            ? 'bg-rose-500/10 border-rose-500/30 text-rose-300'
            : 'bg-slate-900/80 border-slate-700 text-slate-300');
}

function accuracyLabel(acc) {
    if (acc == null) return 'tidak diketahui';
    if (acc <= 20) return 'sangat baik';
    if (acc <= 50) return 'baik';
    if (acc <= 100) return 'sedang, mencari sinyal lebih kuat...';
    return 'rendah, coba pindah ke area terbuka';
}

async function sendLocation(position) {
    const c = position.coords;
    const now = Date.now();

    // Simpan pembacaan paling akurat yang pernah didapat pada sesi ini.
    if (c.accuracy != null && c.accuracy < bestAccuracy) bestAccuracy = c.accuracy;

    // Supaya akurat: jangan kirim setiap event mentah-mentah. Kirim update
    // ketika akurasi membaik dibanding kiriman sebelumnya, atau minimal tiap 4 detik
    // sebagai heartbeat live tracking.
    const shouldSend = (now - lastSentAt > 4000) || (c.accuracy != null && c.accuracy <= bestAccuracy + 5);
    if (!shouldSend) return;
    lastSentAt = now;

    try {
        const res = await fetch('{{ route('device.location', ['token' => $device->device_token]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Device-Platform': navigator.platform || 'browser'
            },
            body: JSON.stringify({
                latitude: c.latitude,
                longitude: c.longitude,
                accuracy: c.accuracy,
                altitude: c.altitude,
                speed: c.speed,
                heading: c.heading
            })
        });
        const json = await res.json();
        if (res.status === 403) {
            // Sudah dihentikan (mungkin dari perangkat/tab lain).
            stopWatch();
            document.getElementById('revokedNotice').classList.remove('hidden');
            setStatus('Berbagi lokasi sudah dihentikan.', 'error');
            return;
        }
        if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mengirim lokasi.');
        setStatus(`<strong>Lokasi aktif dibagikan.</strong><br>Lat: ${c.latitude.toFixed(7)}<br>Lon: ${c.longitude.toFixed(7)}<br>Akurasi: ±${Math.round(c.accuracy)} m (${accuracyLabel(c.accuracy)})<br>Terakhir dikirim: ${new Date().toLocaleTimeString()}`, 'ok');
    } catch (e) {
        setStatus('Gagal mengirim lokasi: ' + e.message, 'error');
    }
}

function startAgent() {
    if (!('geolocation' in navigator)) {
        setStatus('Browser ini tidak mendukung Geolocation API.', 'error');
        return;
    }
    document.getElementById('startBtn').disabled = true;
    document.getElementById('stopBtn').disabled = false;
    sharing = true;
    bestAccuracy = Infinity;
    setStatus('Meminta izin lokasi dari browser...');

    // Ambil fix cepat dulu (single reading) agar peta pengirim langsung terisi,
    // lalu lanjut watchPosition untuk update live yang lebih akurat.
    navigator.geolocation.getCurrentPosition(sendLocation, () => {}, {
        enableHighAccuracy: true,
        timeout: 8000,
        maximumAge: 0
    });

    watchId = navigator.geolocation.watchPosition(sendLocation, (error) => {
        setStatus('Lokasi tidak dapat diakses: ' + error.message, 'error');
        document.getElementById('startBtn').disabled = false;
        document.getElementById('stopBtn').disabled = true;
        sharing = false;
    }, {
        enableHighAccuracy: true,
        maximumAge: 0,
        timeout: 15000
    });
}

function stopWatch() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }
    sharing = false;
    document.getElementById('startBtn').disabled = false;
    document.getElementById('stopBtn').disabled = true;
}

async function stopAndRevoke() {
    stopWatch();
    try {
        await fetch('{{ route('device.revoke', ['token' => $device->device_token]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    } catch (e) { /* tetap hentikan lokal walau request gagal */ }
    document.getElementById('revokedNotice').classList.remove('hidden');
    setStatus('Berbagi lokasi dihentikan. Halaman ini sudah tidak mengirim lokasi Anda.', 'error');
}

window.addEventListener('beforeunload', () => {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
});
</script>
@endpush
