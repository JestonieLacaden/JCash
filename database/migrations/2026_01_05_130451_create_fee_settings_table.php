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
        Schema::create('fee_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('below_500_fee', 8, 2)->default(5);
            $table->decimal('five_hundred_to_999_fee', 8, 2)->default(10);
            $table->decimal('per_1000_fee', 8, 2)->default(15);
            $table->decimal('discounted_per_1000_fee', 8, 2)->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_settings');
    }
};