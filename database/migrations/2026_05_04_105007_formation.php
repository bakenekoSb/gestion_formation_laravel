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
        //les creations de tables se font ici
        Schema::create('formations', function (Blueprint $table) {
            $table->id('id_formation');
            $table->string('titre_formation');
            $table->date('date_debut');
            $table->integer('capacite');
            $table->integer('placeDispo');
            $table->enum('statut', ['en inscription', 'en cours', 'terminee'])->default('en inscription');
        });
    }       

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //les suppressions de tables se font ici
        Schema::dropIfExists('formations');
    }
};
