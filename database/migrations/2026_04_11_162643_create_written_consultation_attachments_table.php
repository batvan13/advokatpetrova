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
        Schema::create('written_consultation_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('written_consultation_request_id');
            $table->foreign('written_consultation_request_id', 'wcr_attachments_request_fk')
                  ->references('id')
                  ->on('written_consultation_requests')
                  ->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('written_consultation_attachments');
    }
};
