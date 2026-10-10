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
        Schema::create('vacante_carrera', function (Blueprint $table) {
        $table->foreignId('vacante_id')->constrained('vacantes')->onDelete('cascade');
        $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
        $table->primary(['vacante_id', 'carrera_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacante_carrera');
    }
};
