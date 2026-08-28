<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoryController extends Controller
{
    /**
     * Halaman Riwayat Pelacakan
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $search = $request->query('search');

        $query = SearchHistory::query()->orderBy('id', 'desc');

        if (!empty($type) && in_array($type, ['ip', 'phone'])) {
            $query->where('type', $type);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('query', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $histories = $query->paginate(15)->withQueryString();
        
        $stats = [
            'total' => SearchHistory::count(),
            'ip_count' => SearchHistory::where('type', 'ip')->count(),
            'phone_count' => SearchHistory::where('type', 'phone')->count()
        ];

        return view('tracker.history', [
            'histories' => $histories,
            'stats' => $stats,
            'currentType' => $type,
            'currentSearch' => $search
        ]);
    }

    /**
     * Dapatkan detail JSON untuk modal view
     */
    public function show($id)
    {
        $history = SearchHistory::findOrFail($id);
        return response()->json($history);
    }

    /**
     * Hapus satu riwayat
     */
    public function destroy($id)
    {
        $history = SearchHistory::findOrFail($id);
        $history->delete();

        return redirect()->route('tracker.history')->with('success', 'Riwayat berhasil dihapus.');
    }

    /**
     * Kosongkan semua riwayat
     */
    public function clear()
    {
        SearchHistory::truncate();

        return redirect()->route('tracker.history')->with('success', 'Semua riwayat berhasil dikosongkan.');
    }

    /**
     * Export data riwayat ke file CSV (Bagus untuk data pendukung tugas akhir)
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'riwayat_pelacakan_ip_phone_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $columns = ['ID', 'Tipe', 'Query Pencarian', 'Ringkasan Hasil', 'IP Pemohon', 'Status', 'Tanggal & Waktu'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            // Menulis UTF-8 BOM agar rapi saat dibuka di Excel Windows
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            SearchHistory::orderBy('id', 'desc')->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->id,
                        strtoupper($row->type),
                        $row->query,
                        $row->title,
                        $row->client_ip,
                        $row->status,
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
