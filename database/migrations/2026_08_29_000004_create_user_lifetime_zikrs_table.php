<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lifetime_zikrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('lifetime_count')->default(0);
            $table->timestamp('last_zikr_at')->nullable();
            $table->timestamps();
        });

        // Backfill existing progress sums for users
        $existingSums = DB::table('user_tasbeeh_progress')
            ->select('user_id', DB::raw('SUM(total_completed) as total_sum'), DB::raw('MAX(last_zikr_at) as latest_zikr'))
            ->groupBy('user_id')
            ->get();

        foreach ($existingSums as $row) {
            DB::table('user_lifetime_zikrs')->insert([
                'user_id' => $row->user_id,
                'lifetime_count' => (int) ($row->total_sum ?? 0),
                'last_zikr_at' => $row->latest_zikr,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lifetime_zikrs');
    }
};

