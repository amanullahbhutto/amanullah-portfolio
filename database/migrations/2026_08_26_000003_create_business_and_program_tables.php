<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('cnic_reference', 100)->nullable();
            $table->decimal('profit_share_percentage', 5, 2)->default(0);
            $table->date('joining_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investor_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('investment_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('profit_periods', function (Blueprint $table): void {
            $table->id();
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('product_cost', 15, 2)->default(0);
            $table->decimal('business_expenses', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('total_investor_profit', 15, 2)->default(0);
            $table->decimal('owner_profit', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['date_from', 'date_to']);
        });

        Schema::create('profit_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profit_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investor_id')->constrained()->restrictOnDelete();
            $table->decimal('profit_percentage', 5, 2);
            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['profit_period_id', 'investor_id']);
        });

        Schema::create('profit_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investor_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('investment_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investor_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('withdrawal_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->date('program_date')->nullable();
            $table->string('location', 180)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('program_contributors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('father_name', 150)->nullable();
            $table->string('from_location', 180)->nullable();
            $table->timestamps();
            $table->index(['program_id', 'name']);
        });

        Schema::create('program_contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contributor_id')->constrained('program_contributors')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('contribution_date');
            $table->text('details')->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('program_expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('paid_to', 180)->nullable();
            $table->text('details')->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('program_contributions');
        Schema::dropIfExists('program_contributors');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('investment_withdrawals');
        Schema::dropIfExists('profit_payments');
        Schema::dropIfExists('profit_allocations');
        Schema::dropIfExists('profit_periods');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('investors');
    }
};
