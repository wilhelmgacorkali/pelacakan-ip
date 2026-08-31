@extends('layouts.app')

@section('title', 'Metodologi & Dokumentasi Teknis')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div class="glass-panel p-6 sm:p-8 rounded-2xl glow-effect">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-3">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Dokumentasi Teknis & Landasan Teori Tugas Akhir</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Metodologi Pelacakan IP & Intelijen Nomor Telepon
        </h1>
        <p class="mt-2 text-slate-300 text-sm leading-relaxed">
            Halaman ini menjelaskan dasar teori, alur arsitektur sistem, perbedaan pelacakan publik vs siber kepolisian, serta batasan teknis dan regulasi privasi data untuk keperluan presentasi dan pengujian tugas kelulusan.
        </p>
    </div>

    <!-- Section 1: IP Geolocation -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <div class="flex items-center space-x-3 text-indigo-400">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                <i class="fa-solid fa-globe"></i>
            </div>
            <h2 class="text-lg font-bold text-white">1. Prinsip Kerja Pelacakan IP (IP Geolocation)</h2>
        </div>

        <div class="text-slate-300 text-sm space-y-3 leading-relaxed">
            <p>
                Pelacakan alamat IP (Internet Protocol) dilakukan dengan memetakan alamat IP publik ke data registrasi yang dikelola oleh <b>Regional Internet Registry (RIR)</b> seperti <i>APNIC</i> (Asia-Pasifik), <i>ARIN</i> (Amerika Utara), dan <i>RIPE NCC</i> (Eropa).
            </p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-800">
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-1 text-indigo-300">Data yang Dihasilkan</h4>
                    <ul class="text-xs space-y-1 text-slate-300 list-disc list-inside">
                        <li>Negara, Provinsi, dan Kota perkiraan</li>
                        <li>Autonomous System Number (ASN) & BGP Route</li>
                        <li>Internet Service Provider (ISP) / Organisasi</li>
                        <li>Status Proxy / VPN / Hosting Datacenter</li>
                    </ul>
                </div>

                <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-800">
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-1 text-amber-300">Batasan Teknis (Limitasi)</h4>
                    <ul class="text-xs space-y-1 text-slate-300 list-disc list-inside">
                        <li>Akurasi koordinat berkisar level <b>Kota / Gateway ISP</b>.</li>
                        <li>Tidak dapat menentukan lokasi alamat rumah atau kamar fisik secara spesifik karena IP publik dialokasikan dinamis oleh DHCP / CGNAT operator.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Phone Intelligence & HLR -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <div class="flex items-center space-x-3 text-emerald-400">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-phone"></i>
            </div>
            <h2 class="text-lg font-bold text-white">2. Prinsip Kerja Intelijen Nomor Telepon (HLR & MSISDN)</h2>
        </div>

        <div class="text-slate-300 text-sm space-y-3 leading-relaxed">
            <p>
                Analisis nomor telepon dilakukan berdasarkan standar internasional <b>ITU-T E.164</b> dan alokasi blok penomoran telekomunikasi nasional dari Kementerian Komunikasi dan Informatika (Kominfo).
            </p>

            <div class="space-y-2">
                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 text-xs">
                    <b class="text-white">A. Ekstraksi MSISDN & HLR (Home Location Register):</b> Sistem memeriksa 4 hingga 6 digit awal (Prefix) nomor telepon untuk mencocokkannya dengan database HLR tempat kartu perdana diterbitkan (misal: 085249 untuk Regional Kalimantan, 081210 untuk Jakarta, 081220 untuk Jawa Barat, dll).
                </div>
                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 text-xs">
                    <b class="text-white">B. Kode Area PSTN (Fixed Line):</b> Untuk jaringan telepon kabel rumah/kantor, sistem memetakan kode wilayah geografis seperti 021 (Jabodetabek), 022 (Bandung), 024 (Semarang), 031 (Surabaya), 061 (Medan), dll.
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Difference between OSINT and Law Enforcement -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 border-l-4 border-amber-500">
        <div class="flex items-center space-x-3 text-amber-400">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <h2 class="text-lg font-bold text-white">3. Perbedaan Pelacakan Publik (OSINT) vs Tim Siber Kepolisian</h2>
        </div>

        <div class="text-slate-300 text-sm space-y-3 leading-relaxed">
            <p>
                Mengapa Kepolisian / Intelijen dapat melacak titik posisi secara presisi sedangkan aplikasi web biasa tidak?
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div class="p-4 bg-slate-900/90 rounded-xl border border-slate-800 space-y-2">
                    <div class="text-indigo-400 font-bold text-xs uppercase flex items-center space-x-1.5">
                        <i class="fa-solid fa-code"></i>
                        <span>Aplikasi Publik / Web OSINT (Aplikasi Ini)</span>
                    </div>
                    <ul class="text-xs space-y-1.5 text-slate-300 list-disc list-inside">
                        <li>Menggunakan data publik (RIR, HLR Prefix, DNS, WHOIS).</li>
                        <li>Menampilkan operator dan wilayah registrasi awal kartu.</li>
                        <li>Tidak memerlukan akses ke jaringan inti operator.</li>
                        <li>Mematuhi etika privasi tanpa menyadap perangkat.</li>
                    </ul>
                </div>

                <div class="p-4 bg-slate-900/90 rounded-xl border border-amber-500/30 bg-amber-950/10 space-y-2">
                    <div class="text-amber-400 font-bold text-xs uppercase flex items-center space-x-1.5">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Tim Siber Kepolisian (Law Enforcement)</span>
                    </div>
                    <ul class="text-xs space-y-1.5 text-slate-300 list-disc list-inside">
                        <li>Memiliki akses <b>Lawful Interception</b> ke Core Network Operator.</li>
                        <li>Menggunakan <b>Triangulasi Sinyal Menara BTS (TDoA)</b>.</li>
                        <li>Menggunakan hardware khusus (*IMSI Catcher / StingRay*).</li>
                        <li>Wajib memiliki Surat Perintah Penyidikan & Izin Pengadilan resmi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Legal & Privacy (UU PDP) -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <div class="flex items-center space-x-3 text-cyan-400">
            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <h2 class="text-lg font-bold text-white">4. Etika Keamanan & Kepatuhan Hukum (UU PDP No. 27/2022)</h2>
        </div>

        <div class="text-slate-300 text-sm space-y-3 leading-relaxed">
            <p>
                Aplikasi ini dikembangkan untuk tujuan akademis, riset keamanan informasi, audit log, serta deteksi anomali jaringan. Data yang diproses merupakan data publik (*Public Threat Intelligence / OSINT*) dan mematuhi prinsip perlindungan privasi data pribadi sesuai regulasi Republik Indonesia.
            </p>
        </div>
    </div>

</div>
@endsection
