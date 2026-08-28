<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use App\Services\IpTrackerService;
use App\Services\PhoneTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackerController extends Controller
{
    protected IpTrackerService $ipService;
    protected PhoneTrackerService $phoneService;

    public function __construct(IpTrackerService $ipService, PhoneTrackerService $phoneService)
    {
        $this->ipService = $ipService;
        $this->phoneService = $phoneService;
    }

    /**
     * Tampilan Utama Dashboard Tracker
     */
    public function index(Request $request)
    {
        $clientIp = $request->ip();
        
        // Ambil riwayat terbaru (5 item) untuk widget recent searches
        $recentSearches = SearchHistory::orderBy('id', 'desc')->take(6)->get();

        return view('tracker.index', [
            'clientIp' => $clientIp,
            'recentSearches' => $recentSearches
        ]);
    }

    /**
     * Endpoint API AJAX: Lacak IP
     */
    public function trackIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip' => 'nullable|string|max:100'
        ]);

        $query = $request->input('ip');
        $data = $this->ipService->track($query);

        if (($data['status'] ?? '') === 'success') {
            // Simpan riwayat pencarian
            $title = ($data['city'] ? $data['city'] . ', ' : '') . $data['country'] . ' (' . $data['isp'] . ')';
            
            SearchHistory::create([
                'type' => 'ip',
                'query' => $data['ip'],
                'title' => $title,
                'result_json' => $data,
                'client_ip' => $request->ip(),
                'status' => 'success'
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $data['message'] ?? 'Gagal mendapatkan data geolokasi IP.'
        ], 422);
    }

    /**
     * Endpoint API AJAX: Lacak Nomor Telepon
     */
    public function trackPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:4|max:25'
        ]);

        $query = $request->input('phone');
        $data = $this->phoneService->track($query);

        if (($data['status'] ?? '') === 'success') {
            // Simpan riwayat pencarian
            $title = $data['carrier'] . ' - ' . $data['country'] . ' (' . $data['e164_format'] . ')';

            SearchHistory::create([
                'type' => 'phone',
                'query' => $data['e164_format'] ?? $query,
                'title' => $title,
                'result_json' => $data,
                'client_ip' => $request->ip(),
                'status' => 'success'
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $data['message'] ?? 'Gagal menganalisis nomor telepon.'
        ], 422);
    }

    /**
     * Halaman Metodologi & Dokumentasi Teknis untuk Ujian / Sidang
     */
    public function docs()
    {
        return view('tracker.docs');
    }
}
