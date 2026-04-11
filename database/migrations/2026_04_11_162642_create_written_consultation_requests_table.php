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
        Schema::create('written_consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('title');
            $table->text('description');
            $table->string('payment_method');
            $table->string('status')->default('submitted');
            $table->decimal('price_eur', 8, 2);
            $table->decimal('price_bgn', 8, 2)->nullable();
            $table->boolean('show_bgn_price')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_consultation_requests');
    }
};
