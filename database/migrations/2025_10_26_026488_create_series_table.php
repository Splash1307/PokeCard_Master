<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('abbreviation', 50)->unique();
            $table->binary('logo')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('series');
    }
};
