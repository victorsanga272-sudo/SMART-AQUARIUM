<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aquariums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vivo_user_id')->unique()->constrained('vivo_users')->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('device_token_hash')->nullable();
            $table->timestamps();
        });

        DB::table('vivo_users')->orderBy('id')->chunkById(500, function ($users): void {
            $timestamp = now();
            $aquariums = $users->map(fn ($user) => [
                'vivo_user_id' => $user->id,
                'public_id' => (string) Str::uuid(),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->all();

            DB::table('aquariums')->insert($aquariums);
        });

        Schema::create('aquarium_telemetry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aquarium_id')->constrained('aquariums')->cascadeOnDelete();
            $table->decimal('temperature', 6, 2);
            $table->decimal('ph', 4, 2);
            $table->decimal('turbidity', 10, 2);
            $table->decimal('water_level', 5, 2)->nullable();
            $table->timestamps();
            $table->index(['aquarium_id', 'created_at']);
        });

        Schema::create('aquarium_actuator_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aquarium_id')->constrained('aquariums')->cascadeOnDelete();
            $table->string('command', 32);
            $table->string('status', 16)->default('pending');
            $table->text('result')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['aquarium_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aquarium_actuator_commands');
        Schema::dropIfExists('aquarium_telemetry');
        Schema::dropIfExists('aquariums');
    }
};