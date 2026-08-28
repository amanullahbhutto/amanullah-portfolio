<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable()->after('visitor_id');
            $table->string('visit_key', 190)->nullable()->after('session_id');
            $table->unique(['session_id', 'visit_key'], 'visitor_logs_session_visit_unique');
        });

        if (Schema::hasTable('projects')) {
            DB::table('projects')
                ->select(['id', 'slug'])
                ->orderBy('id')
                ->get()
                ->each(function ($project): void {
                    DB::table('visitor_logs')
                        ->where('path', '/projects/'.$project->slug)
                        ->update([
                            'project_id' => $project->id,
                            'visit_key' => 'project:'.$project->id,
                        ]);
                });
        }

        DB::table('visitor_logs')
            ->whereNull('visit_key')
            ->orderBy('id')
            ->chunkById(100, function ($visits): void {
                foreach ($visits as $visit) {
                    DB::table('visitor_logs')
                        ->where('id', $visit->id)
                        ->update(['visit_key' => 'path:'.$visit->path]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table): void {
            $table->dropUnique('visitor_logs_session_visit_unique');
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'session_id', 'visit_key']);
        });
    }
};
