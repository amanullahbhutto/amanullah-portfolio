<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('programs') && ! Schema::hasColumn('programs', 'city_id')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->foreignId('city_id')->nullable()->after('location')->constrained('cities')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'city_id')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            });
        }
    }
};

