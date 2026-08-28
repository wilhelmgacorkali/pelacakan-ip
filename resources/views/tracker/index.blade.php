@extends('layouts.app')

@section('title', 'Dashboard Pelacak IP & Nomor Telepon')

@section('content')
<div class="space-y-8">

    <!-- Hero Banner -->
    <div class="glass-panel p-6 sm:p-8 rounded-2xl glow-effect relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-4">
                <i class="fa-solid fa-satellite-dish"></i>
                <span>Open Source Intelligence (OSINT) & Geolocation Tool</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Pelacak Geolokasi IP & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">Intelijen Nomor Telepon / WA</span>
            </h1>
            <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                Aplikasi komprehensif untuk mengidentifikasi titik geolokasi ISP, ASN, koordinat peta interaktif dari alamat IP, serta pemetaan lokasi HLR wilayah kartu, operator seluler, dan toolkit WhatsApp.
            </p>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-800 pb-4">
        <div class="flex items-center space-x-2 bg-slate-900/90 p-1.5 rounded-xl border border-slate-800 w-full sm:w-auto">
            <button id="tab-ip-btn" onclick="switchTab('ip')" class="flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fa-solid fa-globe"></i>
                <span>Pelacak IP Address</span>
            </button>
            <button id="tab-phone-btn" onclick="switchTab('phone')" class="flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fa-solid fa-phone"></i>
                <span>Pelacak Nomor Telepon & WhatsApp</span>
            </button>
        </div>

        <div class="text-xs text-slate-400 flex items-center space-x-2 self-end sm:self-center">
            <span>IP Terdeteksi Saat Ini:</span>
            <span class="font-mono px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-indigo-300 font-semibold">{{ $clientIp }}</span>
        </div>
    </div>

    <!-- ==================== TAB 1: IP TRACKER ==================== -->
    <div id="section-ip" class="space-y-6">
        <!-- Search Input Box -->
        <div class="glass-panel p-5 sm:p-6 rounded-2xl">
            <form id="ipForm" onsubmit="handleIpSearch(event)" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <input type="text" id="ipInput" placeholder="Masukkan IPv4 atau IPv6 (Contoh: 8.8.8.8, 1.1.1.1, atau kosongkan untuk IP sendiri)" 
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-mono text-sm transition-all duration-200">
                    </div>
                    <button type="submit" id="ipSubmitBtn" class="px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 shadow-lg shadow-indigo-600/30 transition-all duration-200">
                        <i class="fa-solid fa-radar"></i>
                        <span>Lacak Alamat IP</span>
                    </button>
                    <button type="button" onclick="trackMyIp()" class="px-4 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl font-medium text-sm flex items-center justify-center space-x-2 border border-slate-700 transition-all duration-200" title="Lacak IP publik Anda sendiri secara langsung">
                        <i class="fa-solid fa-location-crosshairs text-indigo-400"></i>
                        <span class="hidden sm:inline">IP Saya</span>
                    </button>
                </div>

                <!-- Sample Badges -->
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <span>Contoh Cepat:</span>
                    <button type="button" onclick="setAndTrackIp('8.8.8.8')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono transition-colors">8.8.8.8 (Google DNS)</button>
                    <button type="button" onclick="setAndTrackIp('1.1.1.1')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono transition-colors">1.1.1.1 (Cloudflare)</button>
                    <button type="button" onclick="setAndTrackIp('180.252.164.1')" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 font-mono transition-colors">180.252.164.1 (Telkom ID)</button>
                </div>
            </form>
        </div>

        <!-- IP Results Area -->
        <div id="ipResultContainer" class="hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left: Detail Cards (7 Cols) -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Identity Card -->
                    <div class="glass-panel p-5 rounded-xl border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span id="ipFlag" class="text-3xl">🌐</span>
                                <div>
                                    <h3 id="ipDisplay" class="text-xl font-mono font-bold text-white tracking-wide">0.0.0.0</h3>
                                    <p id="ipLocationStr" class="text-xs text-slate-400">Kota, Wilayah, Negara</p>
                                </div>
                            </div>
                            <div id="ipTypeBadge" class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono">
                                IPv4
                            </div>
                        </div>
                    </div>

                    <!-- Technical Detail Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-server text-indigo-400"></i>
                                <span>Penyedia ISP / Operator</span>
                            </div>
                            <div id="ipIsp" class="text-sm font-semibold text-white truncate">-</div>
                        </div>

                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-network-wired text-cyan-400"></i>
                                <span>Autonomous System (ASN)</span>
                            </div>
                            <div id="ipAsn" class="text-sm font-semibold text-white font-mono truncate">-</div>
                        </div>

                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-city text-emerald-400"></i>
                                <span>Kota & Kode Pos</span>
                            </div>
                            <div id="ipCityPostal" class="text-sm font-semibold text-white">-</div>
                        </div>

                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-clock text-amber-400"></i>
                                <span>Zona Waktu (Timezone)</span>
                            </div>
                            <div id="ipTimezone" class="text-sm font-semibold text-white">-</div>
                        </div>

                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-map-pin text-rose-400"></i>
                                <span>Koordinat (Lat, Long)</span>
                            </div>
                            <div id="ipCoordinates" class="text-sm font-semibold text-white font-mono">-</div>
                        </div>

                        <div class="glass-panel p-4 rounded-xl">
                            <div class="flex items-center space-x-2 text-slate-400 text-xs font-medium mb-1">
                                <i class="fa-solid fa-shield-halved text-purple-400"></i>
                                <span>Status Keamanan (Proxy/VPN)</span>
                            </div>
                            <div id="ipProxyStatus" class="text-sm font-semibold text-slate-300">-</div>
                        </div>
                    </div>
                </div>

                <!-- Right: Interactive Map (5 Cols) -->
                <div class="lg:col-span-5 flex flex-col">
                    <div class="glass-panel p-4 rounded-xl flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-3 text-xs">
                            <span class="font-semibold text-slate-300 flex items-center space-x-1.5">
                                <i class="fa-solid fa-map-location-dot text-indigo-400"></i>
                                <span>Peta Geolokasi IP</span>
                            </span>
                            <span class="text-slate-400 font-mono text-[11px]" id="mapCoordLabel">0.0, 0.0</span>
                        </div>
                        <div id="map" class="flex-1 min-h-[300px] rounded-lg border border-slate-700/80"></div>
                        <p class="text-[11px] text-slate-500 mt-2 text-center">
                            *Titik koordinat merepresentasikan BTS/Gateway ISP kota terdekat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: PHONE TRACKER ==================== -->
    <div id="section-phone" class="hidden space-y-6">
        <!-- Phone Search Input Box -->
        <div class="glass-panel p-5 sm:p-6 rounded-2xl">
            <form id="phoneForm" onsubmit="handlePhoneSearch(event)" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>
                        <input type="text" id="phoneInput" placeholder="Masukkan Nomor Telepon / WA (Contoh: 085249621468, 081234567890, atau +6281512345678)" 
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-mono text-sm transition-all duration-200">
                    </div>
                    <button type="submit" id="phoneSubmitBtn" class="px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 shadow-lg shadow-indigo-600/30 transition-all duration-200">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                        <span>Lacak Posisi & Operator</span>
                    </button>
                </div>

                <!-- Sample Provider Badges -->
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <span>Uji Coba Cepat:</span>
                    <button type="button" onclick="setAndTrackPhone('085249621468')" class="px-2.5 py-1 rounded-md bg-red-900/30 border border-red-500/30 hover:bg-red-800/40 text-red-300 font-mono">085249 (Kalsel/Kaltim)</button>
                    <button type="button" onclick="setAndTrackPhone('081210123456')" class="px-2.5 py-1 rounded-md bg-red-900/30 border border-red-500/30 hover:bg-red-800/40 text-red-300 font-mono">081210 (Jakarta)</button>
                    <button type="button" onclick="setAndTrackPhone('081512345678')" class="px-2.5 py-1 rounded-md bg-yellow-900/30 border border-yellow-500/30 hover:bg-yellow-800/40 text-yellow-300 font-mono">0815 (Indosat)</button>
                    <button type="button" onclick="setAndTrackPhone('081787654321')" class="px-2.5 py-1 rounded-md bg-blue-900/30 border border-blue-500/30 hover:bg-blue-800/40 text-blue-300 font-mono">0817 (XL Axiata)</button>
                    <button type="button" onclick="setAndTrackPhone('0215551234')" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 font-mono">021 (Telkom Jakarta)</button>
                </div>
            </form>
        </div>

        <!-- Phone Results Area -->
        <div id="phoneResultContainer" class="hidden space-y-6">
            
            <!-- Grid: Info Cards + Phone Interactive Map -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left: Detail & WhatsApp Toolkit (7 Cols) -->
                <div class="lg:col-span-7 space-y-4">
                    
                    <!-- Card 1: Brand & Operator Status -->
                    <div class="glass-panel p-6 rounded-2xl flex flex-col justify-between relative overflow-hidden border-l-4 border-emerald-500">
                        <div id="carrierAccentBar" class="absolute top-0 left-0 right-0 h-1.5 bg-emerald-500"></div>
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span id="phoneCountryFlag" class="text-3xl">🇮🇩</span>
                                <span id="phoneValidityBadge" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Format Valid
                                </span>
                            </div>
                            <h3 id="phoneCarrierName" class="text-xl font-bold text-white leading-tight">Nama Operator</h3>
                            <p id="phoneCarrierBrand" class="text-xs text-slate-400 mt-1">Brand & Tipe Produk</p>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-800/80 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block mb-0.5">Perusahaan:</span>
                                <span id="phoneCarrierCompany" class="font-semibold text-slate-200">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Tipe Saluran:</span>
                                <span id="phoneLineType" class="font-semibold text-emerald-400">Mobile (Seluler & WA)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Perkiraan Alamat Wilayah & Posisi HLR -->
                    <div class="glass-panel p-5 rounded-xl border border-indigo-500/30 bg-indigo-950/20 space-y-3">
                        <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-indigo-300">
                            <i class="fa-solid fa-map-pin text-indigo-400"></i>
                            <span>Estimasi Alamat & Posisi Wilayah Registrasi:</span>
                        </div>
                        <div id="phoneAddressEstimate" class="text-sm font-semibold text-white leading-relaxed">
                            -
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 pt-1">
                            <span class="flex items-center space-x-1">
                                <i class="fa-solid fa-location-dot text-rose-400"></i>
                                <span id="phoneCoordText" class="font-mono text-slate-300">-</span>
                            </span>
                            <span class="text-slate-600">•</span>
                            <span class="flex items-center space-x-1">
                                <i class="fa-solid fa-city text-cyan-400"></i>
                                <span id="phoneLocation" class="text-slate-300">-</span>
                            </span>
                        </div>
                    </div>

                    <!-- Card 3: WhatsApp Action & Direct Tools -->
                    <div class="glass-panel p-5 rounded-xl border border-emerald-500/30 bg-emerald-950/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-emerald-400 font-bold text-sm">
                                <i class="fa-brands fa-whatsapp text-lg"></i>
                                <span>Akses Lokasi & Kontak WhatsApp</span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">WhatsApp Ready</span>
                        </div>

                        <p class="text-xs text-slate-300 leading-relaxed">
                            Buka chat langsung atau kirim permintaan pembagian titik koordinat GPS terkini (*Share Live Location*) secara resmi dan aman ke nomor ini.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-2 pt-1">
                            <a id="waDirectChatBtn" href="#" target="_blank" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-semibold text-xs flex items-center justify-center space-x-2 transition-colors shadow-lg shadow-emerald-600/20">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>Buka Chat WhatsApp</span>
                            </a>
                            <a id="waShareLocBtn" href="#" target="_blank" class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 rounded-xl font-semibold text-xs flex items-center justify-center space-x-2 transition-colors">
                                <i class="fa-solid fa-location-arrow text-xs"></i>
                                <span>Minta Live Location</span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Right: Phone Interactive Map (5 Cols) -->
                <div class="lg:col-span-5 flex flex-col">
                    <div class="glass-panel p-4 rounded-xl flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-3 text-xs">
                            <span class="font-semibold text-slate-300 flex items-center space-x-1.5">
                                <i class="fa-solid fa-map-location text-emerald-400"></i>
                                <span>Peta Posisi Wilayah Nomor</span>
                            </span>
                            <span class="text-slate-400 font-mono text-[11px]" id="phoneMapCoordLabel">0.0, 0.0</span>
                        </div>
                        <div id="phoneMap" class="flex-1 min-h-[350px] rounded-lg border border-slate-700/80"></div>
                        <p class="text-[11px] text-slate-500 mt-2 text-center">
                            *Menandai pusat sentral wilayah HLR registrasi nomor di Indonesia.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Recent Searches Widget -->
    @if(isset($recentSearches) && count($recentSearches) > 0)
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i>
                <span>Riwayat Pencarian Terkini</span>
            </h3>
            <a href="{{ route('tracker.history') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors font-medium">
                Lihat Semua ({{ count($recentSearches) }}+) &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($recentSearches as $item)
                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800/80 hover:border-indigo-500/30 transition-all flex items-start space-x-3">
                    <div class="mt-0.5 w-7 h-7 rounded-lg {{ $item->type === 'ip' ? 'bg-indigo-500/20 text-indigo-400' : 'bg-emerald-500/20 text-emerald-400' }} flex items-center justify-center text-xs">
                        <i class="fa-solid {{ $item->type === 'ip' ? 'fa-globe' : 'fa-phone' }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-mono text-xs font-bold text-white truncate">{{ $item->query }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $item->title ?? '-' }}</div>
                        <div class="text-[10px] text-slate-500 mt-1">{{ $item->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Tab State Management
    function switchTab(tab) {
        const ipBtn = document.getElementById('tab-ip-btn');
        const phoneBtn = document.getElementById('tab-phone-btn');
        const ipSection = document.getElementById('section-ip');
        const phoneSection = document.getElementById('section-phone');

        if (tab === 'ip') {
            ipBtn.className = 'flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-indigo-600 text-white shadow-md shadow-indigo-600/30';
            phoneBtn.className = 'flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white hover:bg-slate-800';
            ipSection.classList.remove('hidden');
            phoneSection.classList.add('hidden');
            if (mapInstance) {
                setTimeout(() => mapInstance.invalidateSize(), 200);
            }
        } else {
            phoneBtn.className = 'flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 bg-indigo-600 text-white shadow-md shadow-indigo-600/30';
            ipBtn.className = 'flex-1 sm:flex-none flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 text-slate-400 hover:text-white hover:bg-slate-800';
            phoneSection.classList.remove('hidden');
            ipSection.classList.add('hidden');
            if (phoneMapInstance) {
                setTimeout(() => phoneMapInstance.invalidateSize(), 200);
            }
        }
    }

    // ================= LEAFLET MAPS =================
    let mapInstance = null;
    let mapMarker = null;

    let phoneMapInstance = null;
    let phoneMapMarker = null;

    function initOrUpdateIpMap(lat, lon, title, ip) {
        if (!mapInstance) {
            mapInstance = L.map('map', { zoomControl: true, attributionControl: false }).setView([lat, lon], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapInstance);
        } else {
            mapInstance.setView([lat, lon], 12);
        }

        if (mapMarker) mapInstance.removeLayer(mapMarker);

        mapMarker = L.marker([lat, lon]).addTo(mapInstance);
        mapMarker.bindPopup(`
            <div style="font-family: sans-serif; color: #1e293b; font-size: 12px; line-height: 1.4;">
                <b style="color: #4338ca; font-size: 13px;">${ip}</b><br>
                <span>${title}</span><br>
                <span style="color: #64748b; font-size: 11px;">Lat: ${lat}, Lon: ${lon}</span>
            </div>
        `).openPopup();

        document.getElementById('mapCoordLabel').innerText = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
        setTimeout(() => mapInstance.invalidateSize(), 300);
    }

    function initOrUpdatePhoneMap(lat, lon, locationTitle, phoneNumber) {
        if (!phoneMapInstance) {
            phoneMapInstance = L.map('phoneMap', { zoomControl: true, attributionControl: false }).setView([lat, lon], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(phoneMapInstance);
        } else {
            phoneMapInstance.setView([lat, lon], 11);
        }

        if (phoneMapMarker) phoneMapInstance.removeLayer(phoneMapMarker);

        phoneMapMarker = L.marker([lat, lon]).addTo(phoneMapInstance);
        phoneMapMarker.bindPopup(`
            <div style="font-family: sans-serif; color: #1e293b; font-size: 12px; line-height: 1.4;">
                <b style="color: #059669; font-size: 13px;">${phoneNumber}</b><br>
                <span>${locationTitle}</span><br>
                <span style="color: #64748b; font-size: 11px;">Koordinat: ${lat}, ${lon}</span>
            </div>
        `).openPopup();

        document.getElementById('phoneMapCoordLabel').innerText = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
        setTimeout(() => phoneMapInstance.invalidateSize(), 300);
    }

    // ================= IP SEARCH AJAX =================
    async function handleIpSearch(e) {
        if (e) e.preventDefault();
        const ip = document.getElementById('ipInput').value.trim();
        await performIpLookup(ip);
    }

    function trackMyIp() {
        document.getElementById('ipInput').value = '';
        performIpLookup('');
    }

    function setAndTrackIp(sampleIp) {
        document.getElementById('ipInput').value = sampleIp;
        performIpLookup(sampleIp);
    }

    async function performIpLookup(ip) {
        const btn = document.getElementById('ipSubmitBtn');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Melacak...</span>';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('{{ route("api.track.ip") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ip: ip })
            });

            const json = await res.json();

            if (json.success && json.data) {
                renderIpResults(json.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Pencarian Gagal',
                    text: json.message || 'Terjadi kesalahan saat melacak IP.',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#4f46e5'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Gagal terhubung ke server. Periksa koneksi internet Anda.',
                background: '#1e293b',
                color: '#f8fafc',
                confirmButtonColor: '#4f46e5'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    function renderIpResults(data) {
        const container = document.getElementById('ipResultContainer');
        container.classList.remove('hidden');

        document.getElementById('ipDisplay').innerText = data.ip;
        document.getElementById('ipFlag').innerText = data.country_flag || '🌐';
        document.getElementById('ipLocationStr').innerText = `${data.city || 'Kota Tidak Diketahui'}, ${data.region || ''}, ${data.country}`;
        document.getElementById('ipTypeBadge').innerText = data.ip_version || 'IPv4';

        document.getElementById('ipIsp').innerText = data.isp || '-';
        document.getElementById('ipAsn').innerText = data.asn || '-';
        document.getElementById('ipCityPostal').innerText = `${data.city || '-'} (${data.postal_code || '-'})`;
        document.getElementById('ipTimezone').innerText = data.timezone || '-';
        document.getElementById('ipCoordinates').innerText = `${data.latitude}, ${data.longitude}`;

        const proxyEl = document.getElementById('ipProxyStatus');
        if (data.is_proxy || data.is_hosting) {
            proxyEl.innerHTML = '<span class="text-amber-400 font-semibold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Terdeteksi Proxy / Hosting</span>';
        } else {
            proxyEl.innerHTML = '<span class="text-emerald-400 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Residensial / Bersih</span>';
        }

        const lat = parseFloat(data.latitude) || -6.2088;
        const lon = parseFloat(data.longitude) || 106.8456;
        initOrUpdateIpMap(lat, lon, `${data.city}, ${data.country}`, data.ip);

        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ================= PHONE SEARCH AJAX =================
    async function handlePhoneSearch(e) {
        if (e) e.preventDefault();
        const phone = document.getElementById('phoneInput').value.trim();
        await performPhoneLookup(phone);
    }

    function setAndTrackPhone(samplePhone) {
        document.getElementById('phoneInput').value = samplePhone;
        performPhoneLookup(samplePhone);
    }

    async function performPhoneLookup(phone) {
        if (!phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Nomor Masih Kosong',
                text: 'Harap ketikkan nomor telepon atau nomor WA terlebih dahulu.',
                background: '#1e293b',
                color: '#f8fafc',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const btn = document.getElementById('phoneSubmitBtn');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Menganalisis...</span>';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('{{ route("api.track.phone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone: phone })
            });

            const json = await res.json();

            if (json.success && json.data) {
                renderPhoneResults(json.data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: json.message || 'Nomor telepon tidak valid.',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#4f46e5'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Gagal terhubung ke server.',
                background: '#1e293b',
                color: '#f8fafc',
                confirmButtonColor: '#4f46e5'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    function renderPhoneResults(data) {
        const container = document.getElementById('phoneResultContainer');
        container.classList.remove('hidden');

        document.getElementById('phoneCountryFlag').innerText = data.country_flag || '🇮🇩';
        document.getElementById('phoneCarrierName').innerText = data.carrier || 'Operator Tidak Dikenal';
        document.getElementById('phoneCarrierBrand').innerText = data.carrier_brand || 'Layanan Telekomunikasi';
        document.getElementById('phoneCarrierCompany').innerText = data.carrier_company || '-';
        
        if (data.carrier_color) {
            document.getElementById('carrierAccentBar').style.backgroundColor = data.carrier_color;
        }

        const validityBadge = document.getElementById('phoneValidityBadge');
        if (data.is_valid) {
            validityBadge.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
            validityBadge.innerText = 'Format Valid';
        } else {
            validityBadge.className = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30';
            validityBadge.innerText = 'Panjang Digit Tidak Sesuai';
        }

        // Address Estimate & Coords
        document.getElementById('phoneAddressEstimate').innerText = data.address_estimate || data.location || 'Indonesia';
        document.getElementById('phoneCoordText').innerText = data.coordinates_str || '-';
        document.getElementById('phoneLocation').innerText = data.location || '-';

        // WhatsApp Buttons
        document.getElementById('waDirectChatBtn').href = data.whatsapp_link || '#';
        document.getElementById('waShareLocBtn').href = data.whatsapp_request_loc_link || '#';

        // Render Phone Map
        const lat = parseFloat(data.latitude) || -6.2088;
        const lon = parseFloat(data.longitude) || 106.8456;
        initOrUpdatePhoneMap(lat, lon, data.address_estimate || data.location, data.e164_format || data.clean_number);

        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Auto-track user's IP on page load
    document.addEventListener('DOMContentLoaded', () => {
        performIpLookup('');
    });
</script>
@endpush
