<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->string('description', 255)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status',['En attente', 'Actif', 'Inactif'])->default('En attente');
            $table->boolean('was_active')->default(false);
            $table->integer('reward');
        });
    }
    public function down(): void {
        Schema::dropIfExists('challenges');
    }
};
