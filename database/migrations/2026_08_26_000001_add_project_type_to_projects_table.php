<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('projects', 'project_type')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->string('project_type', 60)->default('full_development')->after('github_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'project_type')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->dropColumn('project_type');
            });
        }
    }
};
