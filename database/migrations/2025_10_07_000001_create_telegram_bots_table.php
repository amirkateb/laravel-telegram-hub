<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name')->nullable();
            $table->string('token');
            $table->string('webhook_url')->nullable();
            $table->string('secret_token')->nullable();
            $table->boolean('enabled')->default(true)->index();
            $table->json('allowed_updates')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bots');
    }
};