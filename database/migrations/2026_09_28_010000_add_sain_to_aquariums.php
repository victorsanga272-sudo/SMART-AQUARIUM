<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aquariums', function (Blueprint $table) {
            $table->string('sain', 20)->nullable()->unique();
        });

        DB::table('aquariums')->orderBy('id')->chunkById(500, function ($aquariums): void {
            foreach ($aquariums as $aquarium) {
                do {
                    $sain = '';
                    for ($digit = 0; $digit < 20; $digit++) {
                        $sain .= (string) random_int(0, 9);
                    }
                } while (DB::table('aquariums')->where('sain', $sain)->exists());

                DB::table('aquariums')
                    ->where('id', $aquarium->id)
                    ->update(['sain' => $sain]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('aquariums', function (Blueprint $table) {
            $table->dropUnique(['sain']);
            $table->dropColumn('sain');
        });
    }
};