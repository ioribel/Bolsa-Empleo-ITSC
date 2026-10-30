<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante_formaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained()->cascadeOnDelete();
            $table->index('estudiante_id');
            $table->string('institucion');
            $table->string('titulo');
            $table->enum('nivel', ['bachiller', 'tecnico', 'grado', 'postgrado', 'diplomado', 'otro']);
            $table->string('campo_estudio')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('en_curso')->default(false);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('estudiante_experiencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained()->cascadeOnDelete();
            $table->index('estudiante_id');
            $table->string('empresa');
            $table->string('cargo');
            $table->enum('tipo', ['empleo', 'pasantia', 'voluntariado', 'freelance']);
            $table->string('ubicacion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('actual')->default(false);
            $table->string('descripcion', 1000)->nullable();
            $table->timestamps();
        });

        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->enum('tipo', ['técnica', 'blanda']);
            $table->timestamps();
        });

        Schema::create('estudiante_habilidad', function (Blueprint $table) {
            $table->foreignId('estudiante_id')->constrained()->cascadeOnDelete();
            $table->foreignId('habilidad_id')->constrained('habilidades')->cascadeOnDelete();
            $table->enum('nivel', ['básico', 'intermedio', 'avanzado']);
            $table->primary(['estudiante_id', 'habilidad_id']);
            $table->index('estudiante_id');
            $table->timestamps();
        });

        Schema::create('estudiante_idiomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained()->cascadeOnDelete();
            $table->index('estudiante_id');
            $table->string('idioma');
            $table->enum('nivel', ['básico', 'intermedio', 'avanzado', 'nativo']);
            $table->unique(['estudiante_id', 'idioma']);
            $table->timestamps();
        });

        Schema::create('estudiante_certificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained()->cascadeOnDelete();
            $table->index('estudiante_id');
            $table->string('nombre');
            $table->string('entidad_emisora');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('url_credencial')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_certificaciones');
        Schema::dropIfExists('estudiante_idiomas');
        Schema::dropIfExists('estudiante_habilidad');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('estudiante_experiencias');
        Schema::dropIfExists('estudiante_formaciones');
    }
};
