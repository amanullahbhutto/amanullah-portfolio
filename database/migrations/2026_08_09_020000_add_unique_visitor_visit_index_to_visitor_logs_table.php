<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('visitor_logs')
            ->select('visitor_id', 'visit_key', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as duplicate_count'))
            ->whereNotNull('visit_key')
            ->groupBy('visitor_id', 'visit_key')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($duplicate): void {
                DB::table('visitor_logs')
                    ->where('visitor_id', $duplicate->visitor_id)
                    ->where('visit_key', $duplicate->visit_key)
                    ->where('id', '<>', $duplicate->keep_id)
                    ->delete();
            });

        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->unique(['visitor_id', 'visit_key'], 'visitor_logs_visitor_visit_unique');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->dropUnique('visitor_logs_visitor_visit_unique');
        });
    }
};
