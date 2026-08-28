<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
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

        $stats = [
            'total' => 0,
            'ip_count' => 0,
            'phone_count' => 0
        ];
        $histories = new LengthAwarePaginator([], 0, 15);

        try {
            if (Schema::hasTable('search_histories')) {
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
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }

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
        try {
            $history = SearchHistory::findOrFail($id);
            return response()->json($history);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Not found'], 404);
        }
    }

    /**
     * Hapus satu riwayat
     */
    public function destroy($id)
    {
        try {
            $history = SearchHistory::findOrFail($id);
            $history->delete();
        } catch (\Throwable $e) {
            // Ignore
        }

        return redirect()->route('tracker.history')->with('success', 'Riwayat berhasil dihapus.');
    }

    /**
     * Kosongkan semua riwayat
     */
    public function clear()
    {
        try {
            SearchHistory::truncate();
        } catch (\Throwable $e) {
            // Ignore
        }

        return redirect()->route('tracker.history')->with('success', 'Semua riwayat berhasil dikosongkan.');
    }

    /**
     * Export data riwayat ke file CSV
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
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            try {
                if (Schema::hasTable('search_histories')) {
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
                }
            } catch (\Throwable $e) {
                // Ignore
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
