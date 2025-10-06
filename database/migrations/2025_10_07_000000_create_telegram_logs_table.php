<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telegram_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['outbound','inbound'])->index();
            $table->string('bot_key')->nullable()->index();
            $table->unsignedBigInteger('bot_id')->nullable()->index();
            $table->string('chat_id')->nullable()->index();
            $table->string('message_id')->nullable()->index();
            $table->string('method')->nullable()->index();
            $table->integer('status_code')->nullable();
            $table->boolean('ok')->default(false)->index();
            $table->integer('error_code')->nullable();
            $table->string('error_description')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_logs');
    }
};