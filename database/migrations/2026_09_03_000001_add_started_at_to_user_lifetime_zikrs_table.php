<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_lifetime_zikrs')) {
            if (! Schema::hasColumn('user_lifetime_zikrs', 'started_at')) {
                Schema::table('user_lifetime_zikrs', function (Blueprint $table) {
                    $table->timestamp('started_at')->nullable()->after('lifetime_count');
                });
            }

            // Backfill started_at with earliest tracking_start_date or created_at for existing users
            $users = DB::table('user_lifetime_zikrs')->get();
            foreach ($users as $u) {
                if (! $u->started_at) {
                    $earliestProgress = DB::table('user_tasbeeh_progress')
                        ->where('user_id', $u->user_id)
                        ->whereNotNull('tracking_start_date')
                        ->orderBy('tracking_start_date', 'asc')
                        ->value('tracking_start_date');

                    $startDate = $earliestProgress ?: $u->created_at ?: now();

                    DB::table('user_lifetime_zikrs')
                        ->where('id', $u->id)
                        ->update(['started_at' => $startDate]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_lifetime_zikrs') && Schema::hasColumn('user_lifetime_zikrs', 'started_at')) {
            Schema::table('user_lifetime_zikrs', function (Blueprint $table) {
                $table->dropColumn('started_at');
            });
        }
    }
};

