@extends('layouts.app')

@section('title', 'Riwayat Pelacakan & Log Audit')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Riwayat & Log Pelacakan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Audit log seluruh aktivitas pelacakan IP address dan analisis nomor telepon.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('tracker.history.export') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold flex items-center space-x-2 shadow-lg shadow-emerald-600/20 transition-colors">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Export Data CSV</span>
            </a>
            @if(count($histories) > 0)
            <form action="{{ route('tracker.history.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat?');">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-rose-900/30 hover:bg-rose-800/40 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-semibold flex items-center space-x-1.5 transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Kosongkan Log</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="glass-panel p-4 rounded-xl flex items-center space-x-4 border-l-4 border-indigo-500">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 text-xl">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400 font-medium">Total Aktivitas Pelacakan</div>
                <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="glass-panel p-4 rounded-xl flex items-center space-x-4 border-l-4 border-cyan-500">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 text-xl">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400 font-medium">Pelacakan IP Address</div>
                <div class="text-2xl font-bold text-white">{{ $stats['ip_count'] }}</div>
            </div>
        </div>

        <div class="glass-panel p-4 rounded-xl flex items-center space-x-4 border-l-4 border-emerald-500">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400 font-medium">Analisis Nomor Telepon</div>
                <div class="text-2xl font-bold text-white">{{ $stats['phone_count'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="glass-panel p-4 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Type Filter Pills -->
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <a href="{{ route('tracker.history', ['search' => $currentSearch]) }}" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ empty($currentType) ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Semua ({{ $stats['total'] }})
            </a>
            <a href="{{ route('tracker.history', ['type' => 'ip', 'search' => $currentSearch]) }}" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentType === 'ip' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <i class="fa-solid fa-globe mr-1"></i> IP Address ({{ $stats['ip_count'] }})
            </a>
            <a href="{{ route('tracker.history', ['type' => 'phone', 'search' => $currentSearch]) }}" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $currentType === 'phone' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                <i class="fa-solid fa-phone mr-1"></i> No. Telepon ({{ $stats['phone_count'] }})
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('tracker.history') }}" class="flex items-center space-x-2 w-full md:w-72">
            @if($currentType)
                <input type="hidden" name="type" value="{{ $currentType }}">
            @endif
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Cari IP / No. Telepon..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
            </div>
            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium">
                Cari
            </button>
            @if($currentSearch)
                <a href="{{ route('tracker.history', ['type' => $currentType]) }}" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5 w-12">#</th>
                        <th class="px-4 py-3.5 w-24">Tipe</th>
                        <th class="px-4 py-3.5">Query Pencarian</th>
                        <th class="px-4 py-3.5">Ringkasan Hasil</th>
                        <th class="px-4 py-3.5 w-32">IP Klien</th>
                        <th class="px-4 py-3.5 w-36">Waktu</th>
                        <th class="px-4 py-3.5 w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($histories as $index => $item)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3.5 font-mono text-slate-500">{{ $histories->firstItem() + $index }}</td>
                            <td class="px-4 py-3.5">
                                @if($item->type === 'ip')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                        <i class="fa-solid fa-globe mr-1"></i> IP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        <i class="fa-solid fa-phone mr-1"></i> Telepon
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono font-bold text-white">{{ $item->query }}</td>
                            <td class="px-4 py-3.5 text-slate-300">{{ $item->title ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-400">{{ $item->client_ip ?? '127.0.0.1' }}</td>
                            <td class="px-4 py-3.5 text-slate-400 whitespace-nowrap">{{ $item->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <form action="{{ route('tracker.history.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus entri riwayat ini?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Hapus Riwayat">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="fa-solid fa-inbox text-xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-400">Belum ada riwayat pelacakan.</p>
                                <p class="text-xs text-slate-500 mt-0.5">Lakukan pencarian IP atau nomor telepon pada halaman utama terlebih dahulu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($histories->hasPages())
        <div class="p-4 bg-slate-900/60 border-t border-slate-800">
            {{ $histories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
