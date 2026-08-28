<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khata_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('khata_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khata_customer_id')->constrained('khata_customers')->cascadeOnDelete();
            $table->enum('type', ['pese_liye', 'pese_diye']);
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['khata_customer_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khata_transactions');
        Schema::dropIfExists('khata_customers');
    }
};

