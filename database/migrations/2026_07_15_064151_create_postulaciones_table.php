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
        Schema::create('postulaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vacante_id')->constrained('vacantes')->onDelete('cascade');
        $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
        $table->text('carta_presentacion')->nullable();
        $table->enum('estado', ['recibida', 'en_revision', 'aceptada', 'rechazada'])->default('recibida');
        $table->timestamps();
        $table->unique(['vacante_id', 'estudiante_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
