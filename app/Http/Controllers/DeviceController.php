<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $devices = collect();

        try {
            if (Schema::hasTable('devices')) {
                $devices = Device::query()
                    ->where('is_active', true)
                    ->when($q !== '', function ($query) use ($q) {
                        $query->where(function ($sub) use ($q) {
                            $sub->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%");
                        });
                    })
                    ->orderByDesc('last_seen_at')
                    ->get();
            }
        } catch (\Throwable $e) {
            $devices = collect();
        }

        return view('devices.index', compact('devices', 'q'));
    }

    public function enroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            // Identitas peminta WAJIB diisi. Ini yang ditampilkan ke penerima link
            // supaya mereka tahu persis siapa yang meminta lokasi mereka.
            'requester_name' => ['required', 'string', 'max:120'],
            'requester_photo_url' => ['nullable', 'url', 'max:500'],
            'purpose' => ['nullable', 'string', 'max:200'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json(['success' => false, 'message' => 'Isi email atau nomor HP sebagai identitas perangkat.'], 422);
        }

        try {
            $device = Device::create([
                ...$data,
                'device_token' => Str::random(64),
                'is_active' => true,
                'sharing_enabled' => true,
            ]);

            $agentUrl = route('device.agent', ['token' => $device->device_token]);

            return response()->json([
                'success' => true,
                'device' => $this->devicePayload($device),
                'agent_url' => $agentUrl,
                'token' => $device->device_token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan perangkat: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $device): JsonResponse
    {
        $deviceModel = $device instanceof Device ? $device : Device::find(is_numeric($device) ? (int)$device : $device);
        if ($deviceModel) {
            try {
                $deviceModel->locations()->delete();
                $deviceModel->delete();
            } catch (\Throwable $e) {
                // Ignore
            }
        }
        return response()->json(['success' => true, 'message' => 'Perangkat berhasil dihapus.']);
    }

    public function agent(string $token)
    {
        $device = Device::where('device_token', $token)->where('is_active', true)->firstOrFail();
        return view('devices.agent', compact('device'));
    }

    /**
     * Penerima link menekan tombol ini kapan saja untuk MENGHENTIKAN
     * pembagian lokasi. Ini adalah kontrol milik penerima, bukan pengirim.
     */
    public function revoke(Request $request, string $token): JsonResponse
    {
        $device = Device::where('device_token', $token)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device token tidak ditemukan.'], 404);
        }

        $device->forceFill([
            'sharing_enabled' => false,
            'sharing_revoked_at' => now(),
        ])->save();

        return response()->json(['success' => true, 'message' => 'Berbagi lokasi telah dihentikan.']);
    }

    public function location(Request $request, string $token): JsonResponse
    {
        $device = Device::where('device_token', $token)->where('is_active', true)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device token tidak aktif.'], 404);
        }

        if (!$device->sharing_enabled) {
            return response()->json(['success' => false, 'message' => 'Berbagi lokasi untuk perangkat ini telah dihentikan oleh penerima.'], 403);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'altitude' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
        ]);

        $location = $device->locations()->create([
            ...$data,
            'ip_address' => $this->resolveClientIp($request),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'recorded_at' => now(),
        ]);

        $device->forceFill([
            'last_seen_at' => now(),
            'platform' => substr((string) $request->header('X-Device-Platform'), 0, 40) ?: $device->platform,
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ])->save();

        return response()->json([
            'success' => true,
            'location' => $location,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            ],
        ]);
    }

    public function latest(Request $request, $device): JsonResponse
    {
        $deviceModel = $device instanceof Device ? $device : Device::where('id', is_numeric($device) ? (int)$device : $device)->where('is_active', true)->first();
        if (!$deviceModel) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        }

        $location = $deviceModel->locations()->latest('recorded_at')->first();
        if (!$location) {
            return response()->json([
                'success' => true,
                'device' => $this->devicePayload($deviceModel),
                'location' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'device' => $this->devicePayload($deviceModel),
            'location' => $location,
        ]);
    }

    public function history(Request $request, $device): JsonResponse
    {
        $deviceModel = $device instanceof Device ? $device : Device::where('id', is_numeric($device) ? (int)$device : $device)->where('is_active', true)->firstOrFail();
        $limit = min(max((int) $request->query('limit', 100), 1), 500);
        $locations = $deviceModel->locations()->latest('recorded_at')->limit($limit)->get()->reverse()->values();

        return response()->json([
            'success' => true,
            'device' => $this->devicePayload($deviceModel),
            'locations' => $locations,
        ]);
    }

    private function resolveClientIp(Request $request): string
    {
        $forwardedFor = $request->header('X-Forwarded-For');
        if (is_string($forwardedFor) && trim($forwardedFor) !== '') {
            $ip = trim(explode(',', $forwardedFor)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        $realIp = $request->header('X-Real-IP');
        if (is_string($realIp) && trim($realIp) !== '' && filter_var(trim($realIp), FILTER_VALIDATE_IP)) {
            return trim($realIp);
        }

        return (string) $request->ip();
    }

    private function devicePayload(Device $device): array
    {
        return [
            'id' => $device->id,
            'name' => $device->name,
            'email' => $device->email,
            'phone' => $device->phone,
            'device_token' => $device->device_token,
            'agent_url' => route('device.agent', ['token' => $device->device_token]),
            'platform' => $device->platform,
            'requester_name' => $device->requester_name,
            'requester_photo_url' => $device->requester_photo_url,
            'purpose' => $device->purpose,
            'sharing_enabled' => (bool) $device->sharing_enabled,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'online' => $device->last_seen_at?->gt(now()->subMinutes(2)) ?? false,
        ];
    }
}

