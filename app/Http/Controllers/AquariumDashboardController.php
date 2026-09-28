<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AquariumDashboardController extends Controller
{
    public function latestTelemetry(Request $request)
    {
        $aquarium = $request->user()->aquarium;
        abort_unless($aquarium, 404, 'No aquarium is assigned to this account.');

        $history = $aquarium->telemetry()
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'sain' => $aquarium->sain,
            'current' => $history->last(),
            'history' => $history,
        ]);
    }

    public function queueActuator(Request $request, string $actuator)
    {
        abort_unless(in_array($actuator, ['feeder', 'pump-refill', 'pump-drain'], true), 404);

        $aquarium = $request->user()->aquarium;
        abort_unless($aquarium, 404, 'No aquarium is assigned to this account.');

        $command = $aquarium->actuatorCommands()->create([
            'command' => $actuator,
        ]);

        return response()->json([
            'status' => 'Command queued',
            'command_id' => $command->id,
        ], 202);
    }

    public function pairDevice(Request $request)
    {
        $aquarium = $request->user()->aquarium;
        abort_unless($aquarium, 404, 'No aquarium is assigned to this account.');

        $validated = $request->validate([
            'otp' => ['required', 'string', 'regex:/\A\d{8}\z/'],
        ]);

        $paired = DB::transaction(function () use ($aquarium, $validated): bool {
            $pairing = DB::table('aquarium_device_pairings')
                ->where('aquarium_id', $aquarium->id)
                ->where('otp_hash', hash_hmac('sha256', $validated['otp'], (string) config('app.key')))
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (!$pairing) {
                return false;
            }

            $aquarium->forceFill([
                'device_token_hash' => $pairing->device_token_hash,
            ])->save();

            DB::table('aquarium_device_pairings')->where('id', $pairing->id)->delete();

            return true;
        });

        if (!$paired) {
            return response()->json([
                'message' => 'That pairing code is invalid, expired, or belongs to another aquarium.',
            ], 422);
        }

        return response()->json([
            'sain' => $aquarium->sain,
            'message' => 'ESP32 paired. Its device key is now active for this aquarium.',
        ]);
    }
}