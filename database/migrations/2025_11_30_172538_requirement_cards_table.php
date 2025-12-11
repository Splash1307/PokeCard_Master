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
        Schema::create('requirement_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->contrained('challenge_requirements');
            $table->foreignId('card_id')->constrained('cards');
            $table->integer('required_qty')->default(1);
            $table->unique(['requirement_id', 'card_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_cards');
    }
};
