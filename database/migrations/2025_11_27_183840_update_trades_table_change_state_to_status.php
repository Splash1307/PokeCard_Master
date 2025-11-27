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
        Schema::table('trades', function (Blueprint $table) {
            // Renommer la colonne state en status et modifier les valeurs enum
            $table->dropColumn('state');
        });

        Schema::table('trades', function (Blueprint $table) {
            // Ajouter la nouvelle colonne status avec les bonnes valeurs
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            // Supprimer la colonne status
            $table->dropColumn('status');
        });

        Schema::table('trades', function (Blueprint $table) {
            // Remettre la colonne state
            $table->enum('state', ['start', 'end'])->default('start')->after('id');
        });
    }
};
