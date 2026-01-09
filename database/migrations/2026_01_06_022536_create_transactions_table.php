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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['cash_in', 'cash_out', 'capital_move', 'adjustment']);

            $table->foreignId('gcash_account_id')->nullable()->constrained('gcash_accounts');

            // for capital movement
            $table->foreignId('from_account_id')->nullable()->constrained('gcash_accounts');
            $table->foreignId('to_account_id')->nullable()->constrained('gcash_accounts');

            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->boolean('discounted')->default(false);

            $table->enum('status', ['pending', 'claimed'])->default('pending');
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};