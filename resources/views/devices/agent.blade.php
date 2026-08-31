@extends('layouts.app')

@section('title', 'Device Agent - '.$device->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="glass-panel p-6 sm:p-8 rounded-2xl glow-effect">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-cyan-500 flex items-center justify-center text-white">
                <i class="fa-solid fa-mobile-screen-button text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Device Agent</h1>
                <p class="text-xs text-slate-400">{{ $device->name }}</p>
            </div>
        </div>
        <div id="status" class="p-4 rounded-xl bg-slate-900/80 border border-slate-700 text-sm text-slate-300">
            Menunggu Anda mengaktifkan berbagi lokasi perangkat ini.
        </div>
        <div class="mt-5 flex gap-2">
            <button id="startBtn" onclick="startAgent()" class="flex-1 px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl font-semibold text-sm">
                <i class="fa-solid fa-location-crosshairs mr-2"></i>Aktifkan Lokasi Perangkat
            </button>
        </div>
        <p class="text-xs text-slate-500 mt-4 leading-relaxed">
            Halaman ini memakai Geolocation API browser. Browser akan meminta izin lokasi. Data dikirim ke GeoTrack untuk perangkat yang terdaftar dan dapat dihentikan kapan saja dengan menutup halaman atau mencabut izin lokasi.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let watchId = null;
let sending = false;

function setStatus(message, type = 'normal') {
    const el = document.getElementById('status');
    el.innerHTML = message;
    el.className = 'p-4 rounded-xl border text-sm ' + (type === 'ok'
        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
        : type === 'error'
            ? 'bg-rose-500/10 border-rose-500/30 text-rose-300'
            : 'bg-slate-900/80 border-slate-700 text-slate-300');
}

async function sendLocation(position) {
    if (sending) return;
    sending = true;
    try {
        const c = position.coords;
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
        if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mengirim lokasi.');
        setStatus(`<strong>Lokasi aktif.</strong><br>Lat: ${c.latitude.toFixed(7)}<br>Lon: ${c.longitude.toFixed(7)}<br>Akurasi: ±${Math.round(c.accuracy)} meter<br>Terakhir dikirim: ${new Date().toLocaleTimeString()}`, 'ok');
    } catch (e) {
        setStatus('Gagal mengirim lokasi: ' + e.message, 'error');
    } finally {
        sending = false;
    }
}

function startAgent() {
    if (!('geolocation' in navigator)) {
        setStatus('Browser ini tidak mendukung Geolocation API.', 'error');
        return;
    }
    document.getElementById('startBtn').disabled = true;
    setStatus('Meminta izin lokasi dari browser...');
    watchId = navigator.geolocation.watchPosition(sendLocation, (error) => {
        setStatus('Lokasi tidak dapat diakses: ' + error.message, 'error');
        document.getElementById('startBtn').disabled = false;
    }, {
        enableHighAccuracy: true,
        maximumAge: 5000,
        timeout: 15000
    });
}

window.addEventListener('beforeunload', () => {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
});
</script>
@endpush
