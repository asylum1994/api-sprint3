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
        Schema::create('participas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_juego')->constrained('juegos')->onUpdate('cascade')->onDelete('restrict'); 
            $table->string('telefono');
            $table->string('email');
            $table->string('estado_participa');
            $table->integer('turno')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participas');
    }
};
