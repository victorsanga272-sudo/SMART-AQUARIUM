<?php

namespace App\Http\Controllers;

use App\Models\Aquarium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AquariumDeviceController extends Controller
{
    public function startPairing(Request $request)
    {
        $validated = $request->validate([
            'sain' => ['required', 'string', 'regex:/\A\d{20}\z/', 'exists:aquariums,sain'],
            'otp' => ['required', 'string', 'regex:/\A\d{8}\z/'],
            'device_key' => ['required', 'string', 'regex:/\A[A-Za-z0-9_-]{32}\z/'],
        ]);

        $aquarium = Aquarium::query()->where('sain', $validated['sain'])->firstOrFail();
        $now = now();

        DB::transaction(function () use ($aquarium, $validated, $now): void {
            DB::table('aquarium_device_pairings')
                ->where('aquarium_id', $aquarium->id)
                ->delete();

            DB::table('aquarium_device_pairings')->insert([
                'aquarium_id' => $aquarium->id,
                'otp_hash' => hash_hmac('sha256', $validated['otp'], (string) config('app.key')),
                'device_token_hash' => hash('sha256', $validated['device_key']),
                'expires_at' => $now->copy()->addMinutes(5),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return response()->json([
            'status' => 'waiting_for_user',
            'expires_at' => $now->copy()->addMinutes(5)->toIso8601String(),
        ], 201);
    }

    public function receiveTelemetry(Request $request)
    {
        $aquarium = $this->authenticatedAquarium($request);
        $validated = $request->validate([
            'temperature' => ['required', 'numeric', 'between:-10,100'],
            'ph' => ['required', 'numeric', 'between:0,14'],
            'turbidity' => ['required', 'numeric', 'min:0'],
            'water_level' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $reading = $aquarium->telemetry()->create($validated);

        return response()->json([
            'message' => 'Telemetry received.',
            'reading_id' => $reading->id,
        ], 201);
    }

    public function pendingCommands(Request $request)
    {
        $aquarium = $this->authenticatedAquarium($request);

        $commands = DB::transaction(function () use ($aquarium) {
            $commands = $aquarium->actuatorCommands()
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit(10)
                ->get();

            foreach ($commands as $command) {
                $command->forceFill([
                    'status' => 'dispatched',
                    'dispatched_at' => now(),
                ])->save();
            }

            return $commands;
        });

        return response()->json([
            'commands' => $commands->map(fn ($command) => [
                'id' => $command->id,
                'command' => $command->command,
            ])->values(),
        ]);
    }

    public function completeCommand(Request $request, string $commandId)
    {
        $aquarium = $this->authenticatedAquarium($request);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'failed'])],
            'result' => ['nullable', 'string', 'max:500'],
        ]);

        $command = $aquarium->actuatorCommands()->findOrFail($commandId);
        abort_unless($command->status === 'dispatched', 409, 'Command is not awaiting acknowledgement.');

        $command->forceFill([
            'status' => $validated['status'],
            'result' => $validated['result'] ?? null,
            'completed_at' => now(),
        ])->save();

        return response()->json(['status' => $command->status]);
    }

    private function authenticatedAquarium(Request $request): Aquarium
    {
        $sain = $request->header('X-Aquarium-SAIN');
        $token = $request->bearerToken();
        $aquarium = $sain
            ? Aquarium::query()->where('sain', $sain)->first()
            : null;

        abort_unless(
            $aquarium
            && $aquarium->device_token_hash
            && $token
            && hash_equals($aquarium->device_token_hash, hash('sha256', $token)),
            401,
            'Invalid aquarium credentials.'
        );

        return $aquarium;
    }
}