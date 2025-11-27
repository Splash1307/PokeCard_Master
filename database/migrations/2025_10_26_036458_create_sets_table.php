<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('abbreviation', 50)->unique();
            $table->binary('logo')->nullable();
            $table->foreignId('serie_id')->constrained('series');
        });
    }
    public function down(): void {
        Schema::dropIfExists('sets');
    }
};
