<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aquarium_device_pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aquarium_id')->constrained('aquariums')->cascadeOnDelete();
            $table->char('otp_hash', 64);
            $table->char('device_token_hash', 64);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->index(['aquarium_id', 'otp_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aquarium_device_pairings');
    }
};