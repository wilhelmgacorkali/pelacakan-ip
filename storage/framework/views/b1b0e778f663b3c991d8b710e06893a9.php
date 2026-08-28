<?php $__env->startSection('title', 'Riwayat Pelacakan & Log Audit'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Riwayat & Log Pelacakan</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Audit log seluruh aktivitas pelacakan IP address dan analisis nomor telepon.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?php echo e(route('tracker.history.export')); ?>" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold flex items-center space-x-2 shadow-lg shadow-emerald-600/20 transition-colors">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Export Data CSV</span>
            </a>
            <?php if(count($histories) > 0): ?>
            <form action="<?php echo e(route('tracker.history.clear')); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-3.5 py-2 bg-rose-900/30 hover:bg-rose-800/40 text-rose-300 border border-rose-500/30 rounded-xl text-xs font-semibold flex items-center space-x-1.5 transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Kosongkan Log</span>
                </button>
            </form>
            <?php endif; ?>
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
                <div class="text-2xl font-bold text-white"><?php echo e($stats['total']); ?></div>
            </div>
        </div>

        <div class="glass-panel p-4 rounded-xl flex items-center space-x-4 border-l-4 border-cyan-500">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 text-xl">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400 font-medium">Pelacakan IP Address</div>
                <div class="text-2xl font-bold text-white"><?php echo e($stats['ip_count']); ?></div>
            </div>
        </div>

        <div class="glass-panel p-4 rounded-xl flex items-center space-x-4 border-l-4 border-emerald-500">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400 font-medium">Analisis Nomor Telepon</div>
                <div class="text-2xl font-bold text-white"><?php echo e($stats['phone_count']); ?></div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="glass-panel p-4 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Type Filter Pills -->
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <a href="<?php echo e(route('tracker.history', ['search' => $currentSearch])); ?>" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors <?php echo e(empty($currentType) ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'); ?>">
                Semua (<?php echo e($stats['total']); ?>)
            </a>
            <a href="<?php echo e(route('tracker.history', ['type' => 'ip', 'search' => $currentSearch])); ?>" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors <?php echo e($currentType === 'ip' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'); ?>">
                <i class="fa-solid fa-globe mr-1"></i> IP Address (<?php echo e($stats['ip_count']); ?>)
            </a>
            <a href="<?php echo e(route('tracker.history', ['type' => 'phone', 'search' => $currentSearch])); ?>" 
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors <?php echo e($currentType === 'phone' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'); ?>">
                <i class="fa-solid fa-phone mr-1"></i> No. Telepon (<?php echo e($stats['phone_count']); ?>)
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="<?php echo e(route('tracker.history')); ?>" class="flex items-center space-x-2 w-full md:w-72">
            <?php if($currentType): ?>
                <input type="hidden" name="type" value="<?php echo e($currentType); ?>">
            <?php endif; ?>
            <div class="relative flex-1">
                <input type="text" name="search" value="<?php echo e($currentSearch); ?>" placeholder="Cari IP / No. Telepon..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
            </div>
            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium">
                Cari
            </button>
            <?php if($currentSearch): ?>
                <a href="<?php echo e(route('tracker.history', ['type' => $currentType])); ?>" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs">
                    Reset
                </a>
            <?php endif; ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3.5 font-mono text-slate-500"><?php echo e($histories->firstItem() + $index); ?></td>
                            <td class="px-4 py-3.5">
                                <?php if($item->type === 'ip'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                        <i class="fa-solid fa-globe mr-1"></i> IP
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        <i class="fa-solid fa-phone mr-1"></i> Telepon
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-bold text-white"><?php echo e($item->query); ?></td>
                            <td class="px-4 py-3.5 text-slate-300"><?php echo e($item->title ?? '-'); ?></td>
                            <td class="px-4 py-3.5 font-mono text-slate-400"><?php echo e($item->client_ip ?? '127.0.0.1'); ?></td>
                            <td class="px-4 py-3.5 text-slate-400 whitespace-nowrap"><?php echo e($item->created_at->format('d M Y, H:i')); ?></td>
                            <td class="px-4 py-3.5 text-center">
                                <form action="<?php echo e(route('tracker.history.destroy', $item->id)); ?>" method="POST" onsubmit="return confirm('Hapus entri riwayat ini?');" class="inline-block">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Hapus Riwayat">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="fa-solid fa-inbox text-xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-400">Belum ada riwayat pelacakan.</p>
                                <p class="text-xs text-slate-500 mt-0.5">Lakukan pencarian IP atau nomor telepon pada halaman utama terlebih dahulu.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($histories->hasPages()): ?>
        <div class="p-4 bg-slate-900/60 border-t border-slate-800">
            <?php echo e($histories->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\IP\resources\views/tracker/history.blade.php ENDPATH**/ ?>