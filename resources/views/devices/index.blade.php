@extends('layouts.app')

@section('title', 'Device Tracking & Live Map')

@section('content')
<div class="space-y-6">
    <div class="glass-panel p-6 rounded-2xl glow-effect">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-satellite-dish"></i> Device Tracking
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Perangkat Terdaftar & Live Map</h1>
                <p class="mt-2 text-sm text-slate-400">GPS di peta berasal dari Device Agent yang terdaftar, bukan dari prefix/HLR nomor telepon.</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input name="q" value="{{ $q }}" placeholder="Cari nama, email, atau nomor..." class="px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm w-full lg:w-80 focus:outline-none focus:border-indigo-500">
                <button class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold text-sm">Cari</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 space-y-4">
            <div class="glass-panel p-5 rounded-xl">
                <h2 class="text-sm font-bold text-white mb-4"><i class="fa-solid fa-link mr-2 text-indigo-400"></i>Daftarkan Device</h2>
                <form id="enrollForm" class="space-y-3">
                    <input name="name" required placeholder="Nama perangkat" class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm">
                    <input name="email" type="email" placeholder="Email pemilik/perangkat" class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm">
                    <input name="phone" placeholder="Nomor HP (opsional)" class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm">
                    <button class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-semibold text-sm">Buat Device Agent</button>
                </form>
                <div id="enrollResult" class="hidden mt-4 p-3 rounded-lg bg-slate-900 border border-slate-700 text-xs"></div>
            </div>

            <div class="glass-panel p-3 rounded-xl max-h-[480px] overflow-auto">
                @forelse($devices as $device)
                    <button type="button" onclick="selectDevice({{ $device->id }})" class="device-row w-full text-left p-3 rounded-lg hover:bg-slate-800/80 border border-transparent hover:border-slate-700 mb-1" data-device="{{ $device->id }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-white text-sm truncate">{{ $device->name }}</span>
                            <span class="w-2.5 h-2.5 rounded-full {{ $device->last_seen_at && $device->last_seen_at->gt(now()->subMinutes(2)) ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 truncate mt-1">{{ $device->email ?: ($device->phone ?: 'Tanpa identitas kontak') }}</div>
                    </button>
                @empty
                    <div class="p-5 text-center text-sm text-slate-500">Belum ada device terdaftar.</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-8 glass-panel p-4 rounded-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                <div>
                    <div id="selectedName" class="font-bold text-white">Pilih perangkat</div>
                    <div id="selectedMeta" class="text-xs text-slate-400">Live polling setiap 5 detik</div>
                </div>
                <div id="selectedStatus" class="text-xs text-slate-400">Belum dipilih</div>
            </div>
            <div id="deviceMap" class="min-h-[520px] rounded-lg border border-slate-700/80"></div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3 text-xs">
                <div class="p-3 rounded-lg bg-slate-900/80"><span class="text-slate-500 block">Latitude</span><b id="lat" class="text-white font-mono">-</b></div>
                <div class="p-3 rounded-lg bg-slate-900/80"><span class="text-slate-500 block">Longitude</span><b id="lon" class="text-white font-mono">-</b></div>
                <div class="p-3 rounded-lg bg-slate-900/80"><span class="text-slate-500 block">Accuracy</span><b id="accuracy" class="text-emerald-300">-</b></div>
                <div class="p-3 rounded-lg bg-slate-900/80"><span class="text-slate-500 block">IP terlihat server</span><b id="ip" class="text-indigo-300 font-mono">-</b></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deviceMap = null;
let marker = null;
let selectedId = null;
let timer = null;

function initMap() {
    if (deviceMap) return;
    deviceMap = L.map('deviceMap', { zoomControl: true }).setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(deviceMap);
}

function selectDevice(id) {
    selectedId = id;
    initMap();
    document.querySelectorAll('.device-row').forEach(el => el.classList.remove('bg-indigo-600/10', 'border-indigo-500/30'));
    const row = document.querySelector(`[data-device="${id}"]`);
    if (row) row.classList.add('bg-indigo-600/10', 'border-indigo-500/30');
    refreshDevice();
    clearInterval(timer);
    timer = setInterval(refreshDevice, 5000);
}

async function refreshDevice() {
    if (!selectedId) return;
    try {
        const res = await fetch(`/api/devices/${selectedId}/latest`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Gagal mengambil data');
        const d = json.device;
        document.getElementById('selectedName').innerText = d.name;
        document.getElementById('selectedMeta').innerText = `${d.email || d.phone || 'Device'} • update tiap 5 detik`;
        document.getElementById('selectedStatus').innerHTML = d.online
            ? '<span class="text-emerald-400"><i class="fa-solid fa-circle mr-1 text-[8px]"></i>Online</span>'
            : '<span class="text-slate-500">Offline / belum update</span>';
        if (!json.location) return;
        const l = json.location;
        const lat = Number(l.latitude), lon = Number(l.longitude);
        document.getElementById('lat').innerText = lat.toFixed(7);
        document.getElementById('lon').innerText = lon.toFixed(7);
        document.getElementById('accuracy').innerText = l.accuracy == null ? '-' : `±${Number(l.accuracy).toFixed(1)} m`;
        document.getElementById('ip').innerText = l.ip_address || '-';
        if (!marker) {
            marker = L.marker([lat, lon]).addTo(deviceMap);
        } else {
            marker.setLatLng([lat, lon]);
        }
        marker.bindPopup(`<b>${escapeHtml(d.name)}</b><br>Accuracy ±${l.accuracy ? Number(l.accuracy).toFixed(1) : '-'} m<br>${new Date(l.recorded_at).toLocaleString()}`).openPopup();
        deviceMap.setView([lat, lon], Math.max(deviceMap.getZoom(), 15));
    } catch (e) {
        document.getElementById('selectedStatus').innerText = e.message;
    }
}

function escapeHtml(value) { return String(value).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])); }

document.getElementById('enrollForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = new FormData(e.target);
    const body = Object.fromEntries(form.entries());
    try {
        const res = await fetch('{{ route('devices.enroll') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify(body)
        });
        const json = await res.json();
        const box = document.getElementById('enrollResult');
        box.classList.remove('hidden');
        if (!json.success) throw new Error(json.message || 'Gagal mendaftarkan device.');
        box.innerHTML = `<div class="text-emerald-300 font-semibold mb-2">Device berhasil dibuat.</div><div class="text-slate-400 mb-1">Buka Device Agent pada perangkat yang ingin didaftarkan:</div><a class="text-indigo-300 break-all" target="_blank" href="${json.agent_url}">${json.agent_url}</a>`;
    } catch (err) {
        const box = document.getElementById('enrollResult'); box.classList.remove('hidden'); box.innerHTML = `<span class="text-rose-300">${escapeHtml(err.message)}</span>`;
    }
});

initMap();
@php $first = $devices->first(); @endphp
@if($first)
selectDevice({{ $first->id }});
@endif
</script>
@endpush
