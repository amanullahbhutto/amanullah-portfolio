<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('date_of_births', 'images')) {
            Schema::table('date_of_births', function (Blueprint $table): void {
                $table->json('images')->nullable()->after('end_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('date_of_births', 'images')) {
            Schema::table('date_of_births', function (Blueprint $table): void {
                $table->dropColumn('images');
            });
        }
    }
};
