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
        Schema::create('vacantes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
        $table->string('titulo');
        $table->text('descripcion');
        $table->text('requisitos')->nullable();
        $table->enum('tipo_empleo', ['tiempo_completo', 'medio_tiempo', 'pasantia']);
        $table->enum('modalidad', ['presencial', 'remoto', 'hibrido']);
        $table->string('ubicacion')->nullable();
        $table->decimal('salario_min', 10, 2)->nullable();
        $table->decimal('salario_max', 10, 2)->nullable();
        $table->date('fecha_cierre')->nullable();
        $table->enum('estado', ['borrador', 'publicada', 'cerrada'])->default('borrador');
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};
