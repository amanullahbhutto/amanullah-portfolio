<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('namaz_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fajr_time', 10)->default('05:00');
            $table->string('zuhr_time', 10)->default('13:15');
            $table->string('asr_time', 10)->default('16:45');
            $table->string('maghrib_time', 10)->default('18:50');
            $table->string('isha_time', 10)->default('20:15');
            $table->string('jummah_time', 10)->default('13:30');
            $table->timestamps();
        });

        Schema::create('namaz_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');

            $table->enum('fajr_status', ['jamat', 'without_jamat', 'kaza', 'absent'])->nullable();
            $table->enum('zuhr_status', ['jamat', 'without_jamat', 'kaza', 'absent'])->nullable();
            $table->enum('asr_status', ['jamat', 'without_jamat', 'kaza', 'absent'])->nullable();
            $table->enum('maghrib_status', ['jamat', 'without_jamat', 'kaza', 'absent'])->nullable();
            $table->enum('isha_status', ['jamat', 'without_jamat', 'kaza', 'absent'])->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('namaz_attendances');
        Schema::dropIfExists('namaz_settings');
    }
};

