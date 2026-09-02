<?php

use Illuminate\Support\Facades\Route;

// --- Dashboard & Pelacakan Cepat (IP & Phone) ---
Route::get('/', [App\Http\Controllers\TrackerController::class, 'index'])->name('tracker.index');

Route::post('/api/track/ip', [App\Http\Controllers\TrackerController::class, 'trackIp'])->name('api.track.ip');
Route::post('/track/ip', [App\Http\Controllers\TrackerController::class, 'trackIp']);

Route::post('/api/track/phone', [App\Http\Controllers\TrackerController::class, 'trackPhone'])->name('api.track.phone');
Route::post('/track/phone', [App\Http\Controllers\TrackerController::class, 'trackPhone']);

Route::get('/docs', [App\Http\Controllers\TrackerController::class, 'docs'])->name('tracker.docs');

// --- Riwayat & Export Data ---
Route::get('/history', [App\Http\Controllers\HistoryController::class, 'index'])->name('tracker.history');
Route::get('/history/export/csv', [App\Http\Controllers\HistoryController::class, 'exportCsv'])->name('tracker.history.export');
Route::get('/history/{id}', [App\Http\Controllers\HistoryController::class, 'show'])->name('tracker.history.show');
Route::delete('/history/{id}', [App\Http\Controllers\HistoryController::class, 'destroy'])->name('tracker.history.destroy');
Route::post('/history/clear', [App\Http\Controllers\HistoryController::class, 'clear'])->name('tracker.history.clear');

// --- Device Tracking & Live Map ---
Route::get('/devices', [App\Http\Controllers\DeviceController::class, 'index'])->name('devices.index');

// Pendaftaran perangkat target (Mendukung kedua jalur: /devices/enroll dan /api/devices/enroll)
Route::post('/devices/enroll', [App\Http\Controllers\DeviceController::class, 'enroll']);
Route::post('/api/devices/enroll', [App\Http\Controllers\DeviceController::class, 'enroll'])->name('devices.enroll');

// Halaman agen penerima link
Route::get('/device-agent/{token}', [App\Http\Controllers\DeviceController::class, 'agent'])->name('device.agent');

// Kirim lokasi GPS dari HP target (Mendukung kedua jalur)
Route::post('/api/device-agent/{token}/location', [App\Http\Controllers\DeviceController::class, 'location'])->name('device.location');
Route::post('/device-agent/{token}/location', [App\Http\Controllers\DeviceController::class, 'location']);

// Hentikan/Revoke berbagi lokasi
Route::post('/api/device-agent/{token}/revoke', [App\Http\Controllers\DeviceController::class, 'revoke'])->name('device.revoke');
Route::post('/device-agent/{token}/revoke', [App\Http\Controllers\DeviceController::class, 'revoke']);

// Polling live lokasi & riwayat koordinat
Route::get('/api/devices/{device}/latest', [App\Http\Controllers\DeviceController::class, 'latest'])->name('devices.latest');
Route::get('/devices/{device}/latest', [App\Http\Controllers\DeviceController::class, 'latest']);

Route::get('/api/devices/{device}/history', [App\Http\Controllers\DeviceController::class, 'history'])->name('devices.history');
Route::get('/devices/{device}/history', [App\Http\Controllers\DeviceController::class, 'history']);

// Hapus perangkat
Route::delete('/api/devices/{device}', [App\Http\Controllers\DeviceController::class, 'destroy'])->name('devices.destroy');
Route::match(['DELETE', 'POST'], '/devices/{device}/delete', [App\Http\Controllers\DeviceController::class, 'destroy']);
