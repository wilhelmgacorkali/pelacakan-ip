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
            // Identitas peminta ditampilkan ke penerima link
            'requester_name' => ['nullable', 'string', 'max:120'],
            'requester_photo_url' => ['nullable', 'string', 'max:500'],
            'purpose' => ['nullable', 'string', 'max:200'],
        ]);

        if (empty($data['requester_name'])) {
            $data['requester_name'] = 'Pemilik / Admin';
        }

        if (empty($data['purpose'])) {
            $data['purpose'] = 'Berbagi lokasi real-time';
        }

        $this->ensureTablesExist();

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

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('devices')) {
                Schema::create('devices', function ($table) {
                    $table->id();
                    $table->string('name', 120);
                    $table->string('email', 190)->nullable()->index();
                    $table->string('phone', 30)->nullable()->index();
                    $table->string('device_token', 80)->unique();
                    $table->string('platform', 40)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->timestamp('last_seen_at')->nullable()->index();
                    $table->boolean('is_active')->default(true)->index();
                    $table->string('requester_name', 120)->nullable();
                    $table->string('requester_photo_url', 500)->nullable();
                    $table->string('purpose', 200)->nullable();
                    $table->boolean('sharing_enabled')->default(true);
                    $table->timestamp('sharing_revoked_at')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('device_locations')) {
                Schema::create('device_locations', function ($table) {
                    $table->id();
                    // Gunakan unsignedBigInteger biasa (tanpa foreign key constraint)
                    // agar kompatibel dengan SQLite di Vercel (DB_FOREIGN_KEYS=false)
                    $table->unsignedBigInteger('device_id')->index();
                    $table->decimal('latitude', 10, 7);
                    $table->decimal('longitude', 10, 7);
                    $table->decimal('accuracy', 10, 2)->nullable();
                    $table->decimal('altitude', 10, 2)->nullable();
                    $table->decimal('speed', 10, 2)->nullable();
                    $table->decimal('heading', 10, 2)->nullable();
                    $table->string('ip_address', 45)->nullable()->index();
                    $table->text('user_agent')->nullable();
                    $table->timestamp('recorded_at')->nullable()->index();
                });
            }
        } catch (\Throwable $e) {
            // Ignore
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

