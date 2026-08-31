<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
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

        return view('devices.index', compact('devices', 'q'));
    }

    public function enroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json(['success' => false, 'message' => 'Isi email atau nomor HP sebagai identitas perangkat.'], 422);
        }

        $device = Device::create([
            ...$data,
            'device_token' => Str::random(64),
            'is_active' => true,
        ]);

        $agentUrl = route('device.agent', ['token' => $device->device_token]);

        return response()->json([
            'success' => true,
            'device' => $device,
            'agent_url' => $agentUrl,
        ]);
    }

    public function agent(string $token)
    {
        $device = Device::where('device_token', $token)->where('is_active', true)->firstOrFail();
        return view('devices.agent', compact('device'));
    }

    public function location(Request $request, string $token): JsonResponse
    {
        $device = Device::where('device_token', $token)->where('is_active', true)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device token tidak aktif.'], 404);
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
            'ip_address' => $request->ip(),
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

    public function latest(Request $request, int $device): JsonResponse
    {
        $deviceModel = Device::where('id', $device)->where('is_active', true)->first();
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

    public function history(Request $request, int $device): JsonResponse
    {
        $deviceModel = Device::where('id', $device)->where('is_active', true)->firstOrFail();
        $limit = min(max((int) $request->query('limit', 100), 1), 500);
        $locations = $deviceModel->locations()->latest('recorded_at')->limit($limit)->get()->reverse()->values();

        return response()->json([
            'success' => true,
            'device' => $this->devicePayload($deviceModel),
            'locations' => $locations,
        ]);
    }

    private function devicePayload(Device $device): array
    {
        return [
            'id' => $device->id,
            'name' => $device->name,
            'email' => $device->email,
            'phone' => $device->phone,
            'platform' => $device->platform,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'online' => $device->last_seen_at?->gt(now()->subMinutes(2)) ?? false,
        ];
    }
}
