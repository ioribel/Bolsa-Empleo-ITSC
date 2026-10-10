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
        Schema::create('empresas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('nombre_empresa');
        $table->string('rnc')->unique();
        $table->string('sector')->nullable();
        $table->text('descripcion')->nullable();
        $table->string('telefono')->nullable();
        $table->string('sitio_web')->nullable();
        $table->string('logo_path')->nullable();
        $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'bloqueada'])->default('pendiente');
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
