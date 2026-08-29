<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('zikr_arabic_size')->default(24);
            $table->unsignedSmallInteger('zikr_urdu_size')->default(16);
            $table->boolean('zikr_show_arabic')->default(true);
            $table->boolean('zikr_show_urdu')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'zikr_arabic_size',
                'zikr_urdu_size',
                'zikr_show_arabic',
                'zikr_show_urdu',
            ]);
        });
    }
};

