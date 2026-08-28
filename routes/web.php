<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TrackerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - IP & Phone Tracker
|--------------------------------------------------------------------------
*/

// Halaman Utama & Dashboard
Route::get('/', [TrackerController::class, 'index'])->name('tracker.index');

// API Endpoints untuk AJAX
Route::post('/api/track/ip', [TrackerController::class, 'trackIp'])->name('api.track.ip');
Route::post('/api/track/phone', [TrackerController::class, 'trackPhone'])->name('api.track.phone');

// Halaman Dokumentasi / Metodologi Teknis
Route::get('/docs', [TrackerController::class, 'docs'])->name('tracker.docs');

// Riwayat Pelacakan & Ekspor
Route::get('/history', [HistoryController::class, 'index'])->name('tracker.history');
Route::get('/history/{id}', [HistoryController::class, 'show'])->name('tracker.history.show');
Route::delete('/history/{id}', [HistoryController::class, 'destroy'])->name('tracker.history.destroy');
Route::post('/history/clear', [HistoryController::class, 'clear'])->name('tracker.history.clear');
Route::get('/history/export/csv', [HistoryController::class, 'exportCsv'])->name('tracker.history.export');
