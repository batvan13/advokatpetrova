<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_session_id');
            $table->string('sender_type', 20);
            $table->text('message');
            $table->timestamps();

            $table->foreign('chat_session_id', 'chat_messages_session_fk')
                ->references('id')
                ->on('chat_sessions')
                ->cascadeOnDelete();

            $table->index(['chat_session_id', 'created_at'], 'chat_messages_session_chronological');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
