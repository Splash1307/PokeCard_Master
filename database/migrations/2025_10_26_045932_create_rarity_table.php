<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rarity', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('percentageSpawn');
            $table->integer('price')->default(50);

        });
    }
    public function down(): void {
        Schema::dropIfExists('rarity');
    }
};
