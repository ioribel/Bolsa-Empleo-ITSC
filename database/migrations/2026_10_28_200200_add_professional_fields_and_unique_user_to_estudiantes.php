<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUserIds = DB::table('estudiantes')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicateUserIds) {
            throw new RuntimeException(
                'No se puede agregar UNIQUE a estudiantes.user_id: hay estudiantes duplicados por usuario. '
                .'Resuelve esos registros y vuelve a ejecutar las migraciones.'
            );
        }

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('titular', 120)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('portafolio_url', 255)->nullable();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropUnique('estudiantes_user_id_unique');
            $table->dropColumn(['titular', 'linkedin_url', 'github_url', 'portafolio_url']);
        });
    }
};
