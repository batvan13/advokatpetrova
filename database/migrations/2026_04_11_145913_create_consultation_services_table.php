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
        Schema::create('consultation_services', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->decimal('price_eur', 8, 2);
            $table->decimal('price_bgn', 8, 2);
            $table->decimal('price_eur_60', 8, 2)->nullable();
            $table->decimal('price_bgn_60', 8, 2)->nullable();
            $table->boolean('show_bgn_price')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_services');
    }
};
