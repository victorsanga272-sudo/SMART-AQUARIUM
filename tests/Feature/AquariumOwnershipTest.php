<?php

use App\Models\VivoUsers;
use Illuminate\Support\Facades\Hash;

function createAquariumOwner(string $username, string $email): VivoUsers
{
    return VivoUsers::create([
        'username' => $username,
        'email' => $email,
        'password' => Hash::make('password123'),
    ]);
}

it('limits dashboard telemetry and actuator commands to the authenticated owner', function () {
    $owner = createAquariumOwner('Owner', 'owner@example.com');
    $other = createAquariumOwner('Other', 'other@example.com');

    $owner->aquarium->telemetry()->create([
        'temperature' => 24.5,
        'ph' => 7.2,
        'turbidity' => 4.1,
        'water_level' => 82,
    ]);
    $other->aquarium->telemetry()->create([
        'temperature' => 18.0,
        'ph' => 6.5,
        'turbidity' => 2.0,
        'water_level' => 60,
    ]);

    $this->actingAs($owner)
        ->getJson('/api/telemetry/latest')
        ->assertOk()
        ->assertJsonPath('sain', $owner->aquarium->sain)
        ->assertJsonCount(1, 'history')
        ->assertJsonPath('current.temperature', 24.5);

    $this->actingAs($owner)
        ->postJson('/api/actuator/feeder', ['aquarium_id' => $other->aquarium->id])
        ->assertAccepted();

    $this->assertDatabaseHas('aquarium_actuator_commands', [
        'aquarium_id' => $owner->aquarium->id,
        'command' => 'feeder',
    ]);
    $this->assertDatabaseMissing('aquarium_actuator_commands', [
        'aquarium_id' => $other->aquarium->id,
    ]);

    $this->actingAs($other)
        ->getJson('/api/telemetry/latest')
        ->assertOk()
        ->assertJsonPath('sain', $other->aquarium->sain)
        ->assertJsonPath('current.temperature', 18);
});

it('requires an aquarium-specific key for device telemetry and commands', function () {
    $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

    $owner = createAquariumOwner('DeviceOwner', 'device-owner@example.com');
    $other = createAquariumOwner('DeviceOther', 'device-other@example.com');

    $ownerCommand = $owner->aquarium->actuatorCommands()->create(['command' => 'pump-refill']);
    $otherCommand = $other->aquarium->actuatorCommands()->create(['command' => 'pump-drain']);

    $deviceToken = str_repeat('a', 32);
    $pairingOtp = '48273165';
    $this->postJson('/api/device/pairing/start', [
        'sain' => $owner->aquarium->sain,
        'otp' => $pairingOtp,
        'device_key' => $deviceToken,
    ])->assertCreated();
    $this->assertDatabaseHas('aquarium_device_pairings', [
        'aquarium_id' => $owner->aquarium->id,
        'device_token_hash' => hash('sha256', $deviceToken),
    ]);

    $this->actingAs($other)->postJson('/api/aquarium/pair-device', [
        'otp' => $pairingOtp,
    ])->assertUnprocessable();
    expect($owner->aquarium->fresh()->device_token_hash)->toBeNull();

    $this->actingAs($owner)->postJson('/api/aquarium/pair-device', [
        'otp' => $pairingOtp,
    ])->assertOk()->assertJsonPath('sain', $owner->aquarium->sain);
    $this->actingAs($owner)->postJson('/api/aquarium/pair-device', [
        'otp' => $pairingOtp,
    ])->assertUnprocessable();
    expect($owner->aquarium->fresh()->device_token_hash)->toBe(hash('sha256', $deviceToken));

    $deviceHeaders = [
        'Authorization' => 'Bearer '.$deviceToken,
        'X-Aquarium-SAIN' => $owner->aquarium->sain,
    ];
    $telemetry = [
        'temperature' => 25.3,
        'ph' => 7.4,
        'turbidity' => 3.8,
        'water_level' => 90,
    ];

    $this->postJson('/api/device/telemetry', $telemetry, [
        'Authorization' => 'Bearer '.$deviceToken,
        'X-Aquarium-SAIN' => $other->aquarium->sain,
    ])->assertUnauthorized();

    $this->postJson('/api/device/telemetry', $telemetry, $deviceHeaders)
        ->assertCreated();

    $this->getJson('/api/device/commands', $deviceHeaders)
        ->assertOk()
        ->assertJsonCount(1, 'commands')
        ->assertJsonPath('commands.0.id', $ownerCommand->id)
        ->assertJsonPath('commands.0.command', 'pump-refill');

    $this->postJson('/api/device/commands/'.$otherCommand->id.'/ack', [
        'status' => 'completed',
    ], $deviceHeaders)->assertNotFound();

    $this->postJson('/api/device/commands/'.$ownerCommand->id.'/ack', [
        'status' => 'completed',
    ], $deviceHeaders)->assertOk()->assertJsonPath('status', 'completed');

    $replacementKey = str_repeat('b', 32);
    $replacementOtp = '19384726';
    $this->postJson('/api/device/pairing/start', [
        'sain' => $owner->aquarium->sain,
        'otp' => $replacementOtp,
        'device_key' => $replacementKey,
    ])->assertCreated();
    $this->actingAs($owner)->postJson('/api/aquarium/pair-device', [
        'otp' => $replacementOtp,
    ])->assertOk();

    $this->getJson('/api/device/commands', $deviceHeaders)->assertUnauthorized();
    $this->getJson('/api/device/commands', [
        'Authorization' => 'Bearer '.$replacementKey,
        'X-Aquarium-SAIN' => $owner->aquarium->sain,
    ])->assertOk()->assertJsonCount(0, 'commands');

    $this->assertDatabaseHas('aquarium_telemetry', [
        'aquarium_id' => $owner->aquarium->id,
        'temperature' => 25.3,
    ]);
    $this->assertDatabaseMissing('aquarium_telemetry', [
        'aquarium_id' => $other->aquarium->id,
    ]);
});

it('rejects an expired ESP32 pairing code', function () {
    $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

    $owner = createAquariumOwner('ExpiryOwner', 'expiry-owner@example.com');
    $this->postJson('/api/device/pairing/start', [
        'sain' => $owner->aquarium->sain,
        'otp' => '12345678',
        'device_key' => str_repeat('c', 32),
    ])->assertCreated();

    $this->travel(6)->minutes();

    $this->actingAs($owner)->postJson('/api/aquarium/pair-device', [
        'otp' => '12345678',
    ])->assertUnprocessable();

    expect($owner->aquarium->fresh()->device_token_hash)->toBeNull();
});