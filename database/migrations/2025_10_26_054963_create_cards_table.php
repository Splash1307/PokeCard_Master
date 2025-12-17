<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('localId');
            $table->string('image')->nullable();
            $table->foreignId('set_id')->constrained('sets');
            $table->foreignId('rarity_id')->constrained('rarity');
            $table->foreignId('primaryType_id')->nullable()->constrained('types');
            $table->foreignId('secondaryType_id')->nullable()->constrained('types');
        });
    }

    public function down(): void {
        Schema::dropIfExists('cards');
    }
};
