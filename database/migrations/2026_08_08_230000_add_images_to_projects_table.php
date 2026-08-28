<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('projects', 'images')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->json('images')->nullable();
            });
        }

        DB::table('projects')
            ->whereNotNull('image')
            ->where('image', '<>', '')
            ->whereNull('images')
            ->get(['id', 'image'])
            ->each(function (object $project): void {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['images' => json_encode([$project->image])]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'images')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->dropColumn('images');
            });
        }
    }
};
