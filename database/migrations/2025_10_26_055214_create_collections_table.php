<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->integer('nbCard');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('card_id')->constrained('cards');
        });
    }
    public function down(): void {
        Schema::dropIfExists('collections');
    }
};
