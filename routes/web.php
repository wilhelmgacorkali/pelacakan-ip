<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\TrackerController::class, 'index'])->name('tracker.index');

Route::post('/api/track/ip', [App\Http\Controllers\TrackerController::class, 'trackIp'])->name('api.track.ip');
Route::post('/api/track/phone', [App\Http\Controllers\TrackerController::class, 'trackPhone'])->name('api.track.phone');

Route::get('/docs', [App\Http\Controllers\TrackerController::class, 'docs'])->name('tracker.docs');

Route::get('/history', [App\Http\Controllers\HistoryController::class, 'index'])->name('tracker.history');
Route::get('/history/export/csv', [App\Http\Controllers\HistoryController::class, 'exportCsv'])->name('tracker.history.export');
Route::get('/history/{id}', [App\Http\Controllers\HistoryController::class, 'show'])->name('tracker.history.show');
Route::delete('/history/{id}', [App\Http\Controllers\HistoryController::class, 'destroy'])->name('tracker.history.destroy');
Route::post('/history/clear', [App\Http\Controllers\HistoryController::class, 'clear'])->name('tracker.history.clear');
