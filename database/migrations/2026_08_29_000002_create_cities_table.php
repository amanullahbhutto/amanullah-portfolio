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
        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120)->unique();
                $table->string('state', 120)->nullable();
                $table->string('country', 120)->default('Pakistan');
                $table->string('status', 20)->default('active')->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('program_contributors') && ! Schema::hasColumn('program_contributors', 'city_id')) {
            Schema::table('program_contributors', function (Blueprint $table): void {
                $table->foreignId('city_id')->nullable()->after('from_location')->constrained('cities')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('program_contributors') && Schema::hasColumn('program_contributors', 'city_id')) {
            Schema::table('program_contributors', function (Blueprint $table): void {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            });
        }

        Schema::dropIfExists('cities');
    }
};
