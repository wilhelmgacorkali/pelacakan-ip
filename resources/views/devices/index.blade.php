@extends('layouts.app')

@section('title', 'Device Tracking & Live Map')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="glass-panel p-6 rounded-2xl glow-effect">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-satellite-dish"></i> Device Tracking &amp; Instant Share
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Perangkat Terdaftar &amp; Live Map</h1>
                <p class="mt-2 text-sm text-slate-400">Buat link permintaan lokasi dan langsung kirim ke WhatsApp target atau scan QR Code secara instan.</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input name="q" value="{{ $q }}" placeholder="Cari nama, email, atau nomor..." class="px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm w-full lg:w-80 focus:outline-none focus:border-indigo-500">
                <button class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold text-sm transition-colors">Cari</button>
            </form>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Sidebar Form & List -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Form Registrasi -->
            <div class="glass-panel p-5 rounded-xl border border-slate-700/80">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-emerald-400"></i> Daftarkan Device Target
                    </h2>
                    <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded border border-indigo-500/30 font-medium">1-Click Share</span>
                </div>

                <form id="enrollForm" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Nama Perangkat Target <span class="text-rose-400">*</span></label>
                        <input name="name" required placeholder="mis. HP Adik, iPhone Ayah, Laptop Kantor" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 mb-1">No. WhatsApp Target</label>
                            <input name="phone" id="inputTargetPhone" placeholder="0812xxxx / 62812xxxx" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-400 mb-1">Email Target</label>
                            <input name="email" type="email" placeholder="email@domain.com" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="pt-3 mt-2 border-t border-slate-700/60 space-y-2.5">
                        <div class="flex items-center gap-1.5 text-xs text-indigo-300 font-semibold">
                            <i class="fa-solid fa-user-shield text-[11px]"></i> Identitas Anda (Tampil ke Penerima)
                        </div>
                        <input name="requester_name" required placeholder="Nama Anda (mis. Budi / Kakak)" value="{{ old('requester_name', 'Pemilik / Admin') }}" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-indigo-500">
                        <input name="purpose" placeholder="Tujuan (mis. Pantau perjalanan / Berbagi lokasi)" value="Berbagi lokasi real-time" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-indigo-500">
                        <input name="requester_photo_url" type="url" placeholder="URL Foto Anda (opsional)" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-indigo-500">
                    </div>

                    <button type="submit" id="submitEnrollBtn" class="w-full mt-2 px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Buat Link &amp; Kirim ke Target
                    </button>
                </form>
            </div>

            <!-- List Devices -->
            <div class="glass-panel p-4 rounded-xl border border-slate-700/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-list-check text-indigo-400"></i> Daftar Perangkat (<span id="deviceCount">{{ count($devices) }}</span>)
                    </h3>
                    <span class="text-[11px] text-slate-500">Klik untuk tracking</span>
                </div>

                <div id="deviceListContainer" class="space-y-2 max-h-[440px] overflow-y-auto pr-1">
                    @forelse($devices as $device)
                        <div class="device-row group p-3 rounded-xl bg-slate-900/70 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-800/80 transition-all cursor-pointer relative"
                             data-device="{{ $device->id }}"
                             data-name="{{ $device->name }}"
                             data-phone="{{ $device->phone }}"
                             data-token="{{ $device->device_token }}"
                             data-agent-url="{{ route('device.agent', ['token' => $device->device_token]) }}"
                             onclick="selectDevice({{ $device->id }})">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-sm truncate">{{ $device->name }}</span>
                                        <span class="status-dot w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $device->last_seen_at && $device->last_seen_at->gt(now()->subMinutes(2)) ? 'bg-emerald-400 shadow-sm shadow-emerald-400' : 'bg-slate-600' }}"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5">
                                        {{ $device->phone ?: ($device->email ?: 'Tanpa identitas') }}
                                    </div>
                                </div>

                                <!-- Quick action buttons -->
                                <div class="flex items-center gap-1 opacity-90 sm:opacity-75 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                                    @if($device->phone)
                                        <button type="button" title="Kirim link via WhatsApp" onclick="quickShareWhatsApp('{{ $device->phone }}', '{{ $device->name }}', '{{ $device->device_token }}')" class="p-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white transition-colors text-xs">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </button>
                                    @endif
                                    <button type="button" title="Bagikan Link / QR Code" onclick="openShareModalFromData('{{ $device->name }}', '{{ $device->phone }}', '{{ $device->device_token }}')" class="p-1.5 rounded-lg bg-indigo-500/20 hover:bg-indigo-500 text-indigo-400 hover:text-white transition-colors text-xs">
                                        <i class="fa-solid fa-share-nodes"></i>
                                    </button>
                                    <button type="button" title="Hapus perangkat" onclick="deleteDevice({{ $device->id }}, '{{ $device->name }}')" class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition-colors text-xs">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="noDeviceMsg" class="p-6 text-center text-sm text-slate-500">
                            <i class="fa-solid fa-mobile-screen block text-2xl mb-2 text-slate-600"></i>
                            Belum ada device terdaftar.<br>Gunakan form di atas untuk membuat link pelacakan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Map & Live Stats Panel -->
        <div class="lg:col-span-8 space-y-4">
            <div class="glass-panel p-5 rounded-xl border border-slate-700/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-700/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <i class="fa-solid fa-location-dot text-lg"></i>
                        </div>
                        <div>
                            <div id="selectedName" class="font-extrabold text-white text-base sm:text-lg">Pilih perangkat</div>
                            <div id="selectedMeta" class="text-xs text-slate-400">Live monitoring GPS &amp; status koneksi</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div id="selectedStatus" class="text-xs px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-slate-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span> Belum dipilih
                        </div>
                        <button id="btnShareSelected" onclick="shareCurrentSelected()" class="hidden px-3 py-1.5 bg-indigo-600/30 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-200 hover:text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-share-nodes"></i> Bagikan Link
                        </button>
                    </div>
                </div>

                <!-- Leaflet Live Map -->
                <div class="mt-4 relative">
                    <div id="deviceMap" class="h-[480px] w-full rounded-xl border border-slate-700/80 shadow-inner"></div>
                    <div id="mapOverlayNotice" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm rounded-xl flex items-center justify-center z-[500] text-center p-6 hidden">
                        <div class="max-w-md text-slate-300 space-y-2">
                            <div class="w-12 h-12 rounded-full bg-indigo-600/20 border border-indigo-500/40 mx-auto flex items-center justify-center text-indigo-400 text-xl">
                                <i class="fa-solid fa-hourglass-half animate-spin"></i>
                            </div>
                            <h4 class="font-bold text-white text-base">Menunggu Penerima Membuka Link</h4>
                            <p class="text-xs text-slate-400">Link telah dibuat. Saat target membuka link dan menekan <em>"Aktifkan Lokasi"</em>, koordinat GPS akan langsung muncul secara live di peta ini.</p>
                            <button onclick="shareCurrentSelected()" class="mt-3 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-semibold text-xs transition-colors inline-flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp"></i> Kirim Ulang Link ke Target
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Coordinate Metrics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 mt-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Latitude GPS</span>
                        <b id="lat" class="text-white font-mono text-sm">-</b>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Longitude GPS</span>
                        <b id="lon" class="text-white font-mono text-sm">-</b>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Akurasi GPS</span>
                        <b id="accuracy" class="text-emerald-400 font-mono text-sm">-</b>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider mb-0.5">IP Terlihat Server</span>
                        <b id="ip" class="text-indigo-300 font-mono text-sm">-</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL INSTANT SHARE & QR CODE -->
<!-- ================================================================= -->
<div id="shareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden">
    <div class="glass-panel w-full max-w-lg rounded-2xl p-6 border border-indigo-500/40 shadow-2xl relative animate-in fade-in zoom-in duration-200">
        <!-- Close Button -->
        <button onclick="closeShareModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-lg">
                <i class="fa-solid fa-satellite-dish"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white" id="modalDeviceTitle">Bagikan Link Permintaan Lokasi</h3>
                <p class="text-xs text-slate-400" id="modalDeviceSubtitle">Penerima cukup buka link &amp; tekan izinkan</p>
            </div>
        </div>

        <!-- Mode Domain: Online Vercel vs Localhost -->
        <div class="mb-4 p-3 rounded-xl bg-slate-900/90 border border-slate-800 space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-300">
                <span>Pilih Tipe Link:</span>
                <span class="text-[10px] text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/30">
                    <i class="fa-solid fa-circle-info mr-1"></i>HP Target butuh Link Online
                </span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/80 border border-indigo-500/40 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-600/20">
                    <input type="radio" name="domain_mode" value="online" checked onchange="updateModalUrls()" class="text-indigo-600 focus:ring-0">
                    <div class="min-w-0">
                        <div class="font-bold text-white text-[11px] truncate flex items-center gap-1">
                            <i class="fa-solid fa-globe text-emerald-400"></i> Online (Vercel)
                        </div>
                        <div class="text-[10px] text-slate-400 truncate">Untuk dikirim ke HP target</div>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-800/80 border border-slate-700 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-600/20">
                    <input type="radio" name="domain_mode" value="local" onchange="updateModalUrls()" class="text-indigo-600 focus:ring-0">
                    <div class="min-w-0">
                        <div class="font-bold text-white text-[11px] truncate flex items-center gap-1">
                            <i class="fa-solid fa-laptop text-indigo-400"></i> Lokal (127.0.0.1)
                        </div>
                        <div class="text-[10px] text-slate-400 truncate">Hanya di laptop ini</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Hero Action: Tombol WhatsApp 1-Klik -->
        <div class="mb-4">
            <a id="btnWhatsAppDirect" href="#" target="_blank" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2.5 transition-all">
                <i class="fa-brands fa-whatsapp text-lg"></i>
                <span id="labelWhatsAppBtn">Kirim Langsung ke WhatsApp Target</span>
            </a>
        </div>

        <!-- Input Link & Tombol Copy -->
        <div class="mb-4">
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Link Akses Pelacakan (Siap Dibagikan):</label>
            <div class="flex gap-2">
                <input id="modalShareUrlInput" readonly class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-indigo-300 font-mono text-xs focus:outline-none focus:border-indigo-500 select-all" onclick="this.select()">
                <button type="button" id="btnCopyLink" onclick="copyShareUrl()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fa-solid fa-copy"></i> <span>Salin</span>
                </button>
            </div>
            <p id="copyFeedback" class="text-[11px] text-emerald-400 font-semibold mt-1 hidden"><i class="fa-solid fa-check mr-1"></i>Link berhasil disalin ke clipboard!</p>
        </div>

        <!-- Tampilan QR Code (Bisa langsung discan kamera HP target) -->
        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 text-center mb-4">
            <div class="text-xs font-bold text-slate-300 mb-2 flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-qrcode text-indigo-400"></i> Scan QR Code Ini di HP Target
            </div>
            <div class="flex justify-center my-2">
                <div class="p-2.5 bg-white rounded-xl shadow-lg">
                    <img id="qrCodeImg" src="" alt="QR Code Link" class="w-40 h-40 object-contain">
                </div>
            </div>
            <p class="text-[11px] text-slate-400">Arahkan kamera iPhone / Android ke QR Code ini untuk membuka halaman pelacakan secara langsung.</p>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-800 text-xs">
            <button type="button" id="btnNativeShare" onclick="nativeShare()" class="text-slate-300 hover:text-white font-medium flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-up-from-bracket text-indigo-400"></i> Bagikan via Aplikasi Lain
            </button>
            <button type="button" onclick="closeShareModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-semibold">
                Tutup
            </button>
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
let currentDeviceData = {};

function initMap() {
    if (deviceMap) return;
    deviceMap = L.map('deviceMap', { zoomControl: true }).setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(deviceMap);
}

function selectDevice(id) {
    selectedId = id;
    initMap();
    document.querySelectorAll('.device-row').forEach(el => el.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-600/10'));
    const row = document.querySelector(`[data-device="${id}"]`);
    if (row) {
        row.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-600/10');
        currentShareName = row.dataset.name || '';
        currentSharePhone = row.dataset.phone || '';
        currentShareToken = row.dataset.token || '';
        document.getElementById('btnShareSelected').classList.remove('hidden');
    }
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
        currentDeviceData = d;
        currentShareName = d.name;
        currentSharePhone = d.phone;
        currentShareToken = d.device_token;

        document.getElementById('selectedName').innerText = d.name;
        document.getElementById('selectedMeta').innerText = `${d.phone || d.email || 'Device'} • Peminta: ${d.requester_name || 'Admin'} • Live update 5s`;
        
        const statusEl = document.getElementById('selectedStatus');
        if (d.online) {
            statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> <span class="text-emerald-400 font-bold">Online &amp; Berbagi</span>';
            statusEl.className = 'text-xs px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-1.5';
        } else {
            statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-slate-500"></span> <span class="text-slate-400">Menunggu Lokasi</span>';
            statusEl.className = 'text-xs px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-slate-400 flex items-center gap-1.5';
        }

        const overlayNotice = document.getElementById('mapOverlayNotice');
        if (!json.location) {
            if (overlayNotice) overlayNotice.classList.remove('hidden');
            document.getElementById('lat').innerText = '-';
            document.getElementById('lon').innerText = '-';
            document.getElementById('accuracy').innerText = '-';
            document.getElementById('ip').innerText = '-';
            return;
        }

        if (overlayNotice) overlayNotice.classList.add('hidden');
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
        marker.bindPopup(`<b>${escapeHtml(d.name)}</b><br>Akurasi: ±${l.accuracy ? Number(l.accuracy).toFixed(1) : '-'} m<br>Update: ${new Date(l.recorded_at).toLocaleTimeString()}`).openPopup();
        deviceMap.setView([lat, lon], Math.max(deviceMap.getZoom(), 15));
    } catch (e) {
        document.getElementById('selectedStatus').innerText = e.message;
    }
}

// Modal Share State
let currentShareToken = '';
let currentShareName = '';
let currentSharePhone = '';
let currentShareRequester = '';
let currentSharePurpose = '';

const PUBLIC_PRODUCTION_URL = 'https://pelacakan-nomor.vercel.app';

function getDomainMode() {
    const checked = document.querySelector('input[name="domain_mode"]:checked');
    return checked ? checked.value : 'online';
}

// Generate Best Direct Access URL
function buildBestAgentUrl(token, forceMode = null) {
    const mode = forceMode || getDomainMode();
    const isLocalhost = window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost';

    // Jika mode lokal dipilih secara eksplisit, gunakan origin browser saat ini (127.0.0.1)
    if (mode === 'local') {
        return `${window.location.origin}/device-agent/${token}`;
    }

    // Jika sedang dibuka di Vercel atau domain publik apapun, gunakan origin asli
    if (!isLocalhost && mode !== 'online') {
        return `${window.location.origin}/device-agent/${token}`;
    }

    // Default ke domain online Vercel (wajib jika dikirim ke HP / iPhone / WhatsApp luar)
    return `${PUBLIC_PRODUCTION_URL}/device-agent/${token}`;
}

// Format Phone Number to WhatsApp Format (0812... -> 62812...)
function formatWhatsAppNumber(phone) {
    if (!phone) return '';
    let clean = phone.replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('8')) {
        clean = '628' + clean.slice(1);
    }
    return clean;
}

// Update all URLs in modal when domain mode changes
function updateModalUrls() {
    if (!currentShareToken) return;
    const url = buildBestAgentUrl(currentShareToken);
    document.getElementById('modalShareUrlInput').value = url;

    // WhatsApp Message
    const reqName = currentShareRequester || 'Saya';
    const waText = `Halo! ${reqName} meminta izin untuk memantau lokasi perangkat "${currentShareName}"${currentSharePurpose ? ' untuk ' + currentSharePurpose : ''}.\n\nSilakan klik link aman berikut lalu tekan "Aktifkan & Bagikan Lokasi":\n${url}\n\n(Lokasi hanya dikirim jika Anda menekan tombol izin di halaman tersebut).`;
    
    const waCleanNumber = formatWhatsAppNumber(currentSharePhone);
    const waBtn = document.getElementById('btnWhatsAppDirect');
    const waLabel = document.getElementById('labelWhatsAppBtn');

    if (waCleanNumber) {
        waBtn.href = `https://api.whatsapp.com/send?phone=${waCleanNumber}&text=${encodeURIComponent(waText)}`;
        waLabel.innerText = `Kirim ke WhatsApp (${currentSharePhone})`;
    } else {
        waBtn.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(waText)}`;
        waLabel.innerText = 'Bagikan Pesan ke WhatsApp';
    }

    // QR Code Generator (Instant)
    const qrImg = document.getElementById('qrCodeImg');
    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(url)}&margin=10`;
}

// Open Share Modal
function openShareModal(name, phone, token, requesterName = '', purpose = '') {
    currentShareName = name;
    currentSharePhone = phone;
    currentShareToken = token;
    currentShareRequester = requesterName;
    currentSharePurpose = purpose;

    document.getElementById('modalDeviceTitle').innerText = `Bagikan Link: ${name}`;
    document.getElementById('modalDeviceSubtitle').innerText = phone ? `Target: ${phone}` : 'Kirim link ini ke perangkat target';
    document.getElementById('copyFeedback').classList.add('hidden');

    // Auto-set radio to 'online' by default so target phone works
    const onlineRadio = document.querySelector('input[name="domain_mode"][value="online"]');
    if (onlineRadio) onlineRadio.checked = true;

    updateModalUrls();
    document.getElementById('shareModal').classList.remove('hidden');
}

function openShareModalFromData(name, phone, token) {
    openShareModal(name, phone, token);
}

function shareCurrentSelected() {
    if (!currentShareToken && selectedId) {
        const row = document.querySelector(`[data-device="${selectedId}"]`);
        if (row) {
            currentShareName = row.dataset.name;
            currentSharePhone = row.dataset.phone;
            currentShareToken = row.dataset.token;
        }
    }
    if (currentShareToken) {
        openShareModal(currentShareName, currentSharePhone, currentShareToken, currentDeviceData.requester_name || '', currentDeviceData.purpose || '');
    }
}

function quickShareWhatsApp(phone, name, token) {
    // Selalu gunakan online Vercel URL untuk quick WhatsApp share
    const url = buildBestAgentUrl(token, 'online');
    const waClean = formatWhatsAppNumber(phone);
    const waText = `Halo! Saya meminta izin untuk berbagi lokasi perangkat "${name}".\n\nSilakan buka link berikut dan tekan "Aktifkan Lokasi":\n${url}`;
    window.open(`https://api.whatsapp.com/send?phone=${waClean}&text=${encodeURIComponent(waText)}`, '_blank');
}

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
}

function copyShareUrl() {
    const input = document.getElementById('modalShareUrlInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const fb = document.getElementById('copyFeedback');
        fb.classList.remove('hidden');
        setTimeout(() => fb.classList.add('hidden'), 3500);
    });
}

function nativeShare() {
    const url = document.getElementById('modalShareUrlInput').value;
    if (navigator.share) {
        navigator.share({
            title: `Permintaan Berbagi Lokasi: ${currentShareName}`,
            text: `Permintaan berbagi lokasi perangkat ${currentShareName}. Buka link berikut:`,
            url: url
        }).catch(() => {});
    } else {
        copyShareUrl();
        alert('Link telah disalin. Anda dapat menempelkannya di aplikasi chat pilihan Anda.');
    }
}

async function deleteDevice(id, name) {
    const confirm = await Swal.fire({
        title: `Hapus ${escapeHtml(name)}?`,
        text: 'Riwayat lokasi perangkat ini akan ikut dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#334155',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        background: '#0f172a',
        color: '#f8fafc'
    });

    if (confirm.isConfirmed) {
        try {
            const res = await fetch(`/api/devices/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const json = await res.json();
            if (json.success) {
                const row = document.querySelector(`[data-device="${id}"]`);
                if (row) row.remove();
                if (selectedId === id) {
                    selectedId = null;
                    document.getElementById('selectedName').innerText = 'Pilih perangkat';
                    document.getElementById('selectedStatus').innerHTML = 'Belum dipilih';
                    document.getElementById('lat').innerText = '-';
                    document.getElementById('lon').innerText = '-';
                    if (marker && deviceMap) {
                        deviceMap.removeLayer(marker);
                        marker = null;
                    }
                }
                const countEl = document.getElementById('deviceCount');
                if (countEl) {
                    const currentCount = document.querySelectorAll('.device-row').length;
                    countEl.innerText = currentCount;
                }
                Swal.fire({
                    title: 'Berhasil',
                    text: 'Perangkat telah dihapus.',
                    icon: 'success',
                    background: '#0f172a',
                    color: '#f8fafc',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        } catch (e) {
            Swal.fire({ title: 'Gagal', text: e.message, icon: 'error', background: '#0f172a', color: '#f8fafc' });
        }
    }
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
}

// Enroll Form Submission
document.getElementById('enrollForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitEnrollBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Membuat Link...';

    const form = new FormData(e.target);
    const body = Object.fromEntries(form.entries());

    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.content : '';

        const res = await fetch('/api/devices/enroll', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(body)
        });

        const json = await res.json().catch(() => ({ success: false, message: 'Respon server tidak valid.' }));
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Buat Link &amp; Kirim ke Target';

        if (!json.success) {
            let errorMsg = json.message || 'Gagal mendaftarkan device.';
            if (json.errors) {
                errorMsg = Object.values(json.errors).flat().join(', ');
            }
            throw new Error(errorMsg);
        }

        const d = json.device;
        const noDevMsg = document.getElementById('noDeviceMsg');
        if (noDevMsg) noDevMsg.remove();

        // Tambahkan ke list UI secara instan
        const listContainer = document.getElementById('deviceListContainer');
        const newRow = document.createElement('div');
        newRow.className = 'device-row group p-3 rounded-xl bg-slate-900/70 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-800/80 transition-all cursor-pointer relative';
        newRow.setAttribute('data-device', d.id);
        newRow.setAttribute('data-name', d.name);
        newRow.setAttribute('data-phone', d.phone || '');
        newRow.setAttribute('data-token', d.device_token);
        newRow.setAttribute('data-agent-url', buildBestAgentUrl(d.device_token));
        newRow.onclick = () => selectDevice(d.id);

        newRow.innerHTML = `
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white text-sm truncate">${escapeHtml(d.name)}</span>
                        <span class="status-dot w-2.5 h-2.5 rounded-full flex-shrink-0 bg-slate-600"></span>
                    </div>
                    <div class="text-[11px] text-slate-400 truncate mt-0.5">${escapeHtml(d.phone || d.email || 'Tanpa kontak')}</div>
                </div>
                <div class="flex items-center gap-1 opacity-90 sm:opacity-75 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                    ${d.phone ? `<button type="button" title="Kirim via WhatsApp" onclick="quickShareWhatsApp('${escapeHtml(d.phone)}', '${escapeHtml(d.name)}', '${escapeHtml(d.device_token)}')" class="p-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white transition-colors text-xs"><i class="fa-brands fa-whatsapp"></i></button>` : ''}
                    <button type="button" title="Bagikan Link / QR" onclick="openShareModalFromData('${escapeHtml(d.name)}', '${escapeHtml(d.phone || '')}', '${escapeHtml(d.device_token)}')" class="p-1.5 rounded-lg bg-indigo-500/20 hover:bg-indigo-500 text-indigo-400 hover:text-white transition-colors text-xs"><i class="fa-solid fa-share-nodes"></i></button>
                    <button type="button" title="Hapus" onclick="deleteDevice(${d.id}, '${escapeHtml(d.name)}')" class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition-colors text-xs"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
        listContainer.prepend(newRow);

        const countEl = document.getElementById('deviceCount');
        if (countEl) countEl.innerText = document.querySelectorAll('.device-row').length;

        // Reset input nama target di form
        const nameInput = e.target.querySelector('input[name="name"]');
        if (nameInput) nameInput.value = '';
        const phoneInput = e.target.querySelector('input[name="phone"]');
        if (phoneInput) phoneInput.value = '';
        const emailInput = e.target.querySelector('input[name="email"]');
        if (emailInput) emailInput.value = '';

        // Pilih device baru & buka modal share langsung!
        selectDevice(d.id);
        openShareModal(d.name, d.phone, d.device_token, d.requester_name, d.purpose);

    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Buat Link &amp; Kirim ke Target';
        Swal.fire({
            title: 'Perhatian',
            text: err.message,
            icon: 'warning',
            background: '#0f172a',
            color: '#f8fafc'
        });
    }
});

// Inisialisasi awal
initMap();
@php $first = $devices->first(); @endphp
@if($first)
selectDevice({{ $first->id }});
@endif
</script>
@endpush
