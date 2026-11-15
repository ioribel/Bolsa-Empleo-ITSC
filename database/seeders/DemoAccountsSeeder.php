<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Empresa;
use App\Models\Estudiante;
use App\Models\Habilidad;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Create local-only demo accounts and professional profiles.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }

        $accounts = [
            [
                'type' => 'Empresa',
                'email' => 'empresa.demo01@example.test',
                'name' => 'Cuenta Demo Empresa 01',
                'company' => [
                    'nombre_empresa' => 'Nube Caribe Labs',
                    'rnc' => 'DEMO-RNC-001',
                    'sector' => 'Tecnología',
                    'descripcion' => 'Empresa de demostración enfocada en soluciones web y servicios digitales.',
                    'telefono' => '+1-809-555-0101',
                    'sitio_web' => 'https://example.com/nube-caribe',
                    'estado' => 'aprobada',
                ],
            ],
            [
                'type' => 'Empresa',
                'email' => 'empresa.demo02@example.test',
                'name' => 'Cuenta Demo Empresa 02',
                'company' => [
                    'nombre_empresa' => 'Soluciones Delta Digital',
                    'rnc' => 'DEMO-RNC-002',
                    'sector' => 'Servicios profesionales',
                    'descripcion' => 'Equipo de demostración dedicado a soporte, redes y transformación digital.',
                    'telefono' => '+1-809-555-0102',
                    'sitio_web' => 'https://example.com/delta-digital',
                    'estado' => 'aprobada',
                ],
            ],
            [
                'type' => 'Empresa',
                'email' => 'empresa.demo03@example.test',
                'name' => 'Cuenta Demo Empresa 03',
                'company' => [
                    'nombre_empresa' => 'Estudio Creativo Horizonte',
                    'rnc' => 'DEMO-RNC-003',
                    'sector' => 'Diseño y comunicación',
                    'descripcion' => 'Estudio ficticio para probar perfiles de empresa y publicación de vacantes.',
                    'telefono' => '+1-809-555-0103',
                    'sitio_web' => 'https://example.com/horizonte-creativo',
                    'estado' => 'aprobada',
                ],
            ],
            [
                'type' => 'Estudiante',
                'email' => 'estudiante.demo01@example.test',
                'name' => 'Valeria Estudiante Demo',
                'student' => [
                    'matricula' => 'DEMO-EST-001',
                    'tipo' => 'estudiante',
                    'telefono' => '+1-809-555-0201',
                    'carrera' => 'Desarrollo de Software',
                    'titular' => 'Desarrolladora junior | Laravel y aplicaciones web',
                    'resumen' => 'Estudiante de desarrollo de software con experiencia académica creando aplicaciones web, diseñando APIs y trabajando con bases de datos relacionales.',
                    'portafolio_url' => 'https://example.com/portafolio/valeria-demo',
                ],
                'formation' => [
                    'institucion' => 'ITSC',
                    'titulo' => 'Técnico Superior en Desarrollo de Software',
                    'nivel' => 'tecnico',
                    'campo_estudio' => 'Desarrollo de Software',
                    'fecha_inicio' => today()->subYears(2)->toDateString(),
                    'fecha_fin' => today()->addMonths(6)->toDateString(),
                    'en_curso' => true,
                    'descripcion' => 'Formación en programación, diseño de bases de datos y desarrollo de aplicaciones.',
                ],
                'experience' => [
                    'empresa' => 'Proyecto Académico Demo',
                    'cargo' => 'Desarrolladora web en práctica',
                    'tipo' => 'pasantia',
                    'ubicacion' => 'Santo Domingo Este',
                    'fecha_inicio' => today()->subMonths(8)->toDateString(),
                    'fecha_fin' => null,
                    'actual' => true,
                    'descripcion' => 'Colaboración en una aplicación web de demostración con Laravel y MySQL.',
                ],
                'skills' => [
                    ['nombre' => 'php', 'tipo' => 'técnica', 'nivel' => 'intermedio'],
                    ['nombre' => 'laravel', 'tipo' => 'técnica', 'nivel' => 'intermedio'],
                    ['nombre' => 'mysql', 'tipo' => 'técnica', 'nivel' => 'básico'],
                    ['nombre' => 'trabajo en equipo', 'tipo' => 'blanda', 'nivel' => 'avanzado'],
                ],
                'language' => ['idioma' => 'Inglés', 'nivel' => 'intermedio'],
                'certification' => [
                    'nombre' => 'Fundamentos de desarrollo web',
                    'entidad_emisora' => 'Certificación de demostración',
                    'fecha_emision' => today()->subMonths(5)->toDateString(),
                ],
            ],
            [
                'type' => 'Estudiante',
                'email' => 'estudiante.demo02@example.test',
                'name' => 'Mateo Estudiante Demo',
                'student' => [
                    'matricula' => 'DEMO-EST-002',
                    'tipo' => 'egresado',
                    'telefono' => '+1-809-555-0202',
                    'carrera' => 'Administración de Redes',
                    'titular' => 'Técnico de redes | Linux y soporte informático',
                    'resumen' => 'Egresado de administración de redes con práctica en configuración de redes, soporte técnico, sistemas Linux y documentación de incidencias.',
                    'portafolio_url' => 'https://example.com/portafolio/mateo-demo',
                ],
                'formation' => [
                    'institucion' => 'ITSC',
                    'titulo' => 'Técnico Superior en Administración de Redes',
                    'nivel' => 'tecnico',
                    'campo_estudio' => 'Redes y sistemas',
                    'fecha_inicio' => today()->subYears(3)->toDateString(),
                    'fecha_fin' => today()->subMonths(10)->toDateString(),
                    'en_curso' => false,
                    'descripcion' => 'Estudios de administración de redes, sistemas operativos y soporte de infraestructura.',
                ],
                'experience' => [
                    'empresa' => 'Laboratorio de Redes Demo',
                    'cargo' => 'Asistente de soporte técnico',
                    'tipo' => 'empleo',
                    'ubicacion' => 'Santo Domingo',
                    'fecha_inicio' => today()->subMonths(10)->toDateString(),
                    'fecha_fin' => today()->subMonths(2)->toDateString(),
                    'actual' => false,
                    'descripcion' => 'Atención de incidencias de práctica y documentación de procedimientos de soporte.',
                ],
                'skills' => [
                    ['nombre' => 'linux', 'tipo' => 'técnica', 'nivel' => 'intermedio'],
                    ['nombre' => 'redes informáticas', 'tipo' => 'técnica', 'nivel' => 'avanzado'],
                    ['nombre' => 'soporte técnico', 'tipo' => 'técnica', 'nivel' => 'avanzado'],
                    ['nombre' => 'resolución de problemas', 'tipo' => 'blanda', 'nivel' => 'intermedio'],
                ],
                'language' => ['idioma' => 'Inglés', 'nivel' => 'básico'],
                'certification' => [
                    'nombre' => 'Fundamentos de redes',
                    'entidad_emisora' => 'Certificación de demostración',
                    'fecha_emision' => today()->subMonths(12)->toDateString(),
                ],
            ],
            [
                'type' => 'Estudiante',
                'email' => 'estudiante.demo03@example.test',
                'name' => 'Camila Estudiante Demo',
                'student' => [
                    'matricula' => 'DEMO-EST-003',
                    'tipo' => 'estudiante',
                    'telefono' => '+1-809-555-0203',
                    'carrera' => 'Diseño Gráfico',
                    'titular' => 'Diseñadora gráfica junior | Identidad visual y contenido digital',
                    'resumen' => 'Estudiante de diseño gráfico interesada en identidad visual, composición y creación de contenido para medios digitales.',
                    'portafolio_url' => 'https://example.com/portafolio/camila-demo',
                ],
                'formation' => [
                    'institucion' => 'ITSC',
                    'titulo' => 'Técnico Superior en Diseño Gráfico',
                    'nivel' => 'tecnico',
                    'campo_estudio' => 'Diseño Gráfico',
                    'fecha_inicio' => today()->subYears(2)->toDateString(),
                    'fecha_fin' => today()->addMonths(8)->toDateString(),
                    'en_curso' => true,
                    'descripcion' => 'Formación práctica en comunicación visual, tipografía y producción de piezas gráficas.',
                ],
                'experience' => [
                    'empresa' => 'Taller Creativo Demo',
                    'cargo' => 'Asistente de diseño',
                    'tipo' => 'freelance',
                    'ubicacion' => 'Santo Domingo Este',
                    'fecha_inicio' => today()->subMonths(7)->toDateString(),
                    'fecha_fin' => today()->subMonths(1)->toDateString(),
                    'actual' => false,
                    'descripcion' => 'Creación de materiales gráficos de práctica para campañas y proyectos académicos.',
                ],
                'skills' => [
                    ['nombre' => 'diseño gráfico', 'tipo' => 'técnica', 'nivel' => 'avanzado'],
                    ['nombre' => 'creatividad', 'tipo' => 'blanda', 'nivel' => 'avanzado'],
                    ['nombre' => 'comunicación', 'tipo' => 'blanda', 'nivel' => 'intermedio'],
                    ['nombre' => 'gestión del tiempo', 'tipo' => 'blanda', 'nivel' => 'intermedio'],
                ],
                'language' => ['idioma' => 'Inglés', 'nivel' => 'intermedio'],
                'certification' => [
                    'nombre' => 'Principios de diseño visual',
                    'entidad_emisora' => 'Certificación de demostración',
                    'fecha_emision' => today()->subMonths(4)->toDateString(),
                ],
            ],
        ];

        foreach ($accounts as &$account) {
            $account['password'] = Str::password(24);
        }
        unset($account);

        $credentials = DB::transaction(function () use ($accounts): array {
            $credentials = [];

            foreach ($accounts as $account) {
                $user = User::updateOrCreate(
                    ['email' => $account['email']],
                    [
                        'name' => $account['name'],
                        'password' => Hash::make($account['password']),
                        'rol' => strtolower($account['type']),
                        'activo' => true,
                        'email_verified_at' => now(),
                    ]
                );

                if ($account['type'] === 'Empresa') {
                    $user->empresa()->updateOrCreate([], $account['company']);
                } else {
                    $career = Carrera::query()
                        ->where('nombre', $account['student']['carrera'])
                        ->firstOrFail();

                    $studentData = $account['student'];
                    unset($studentData['carrera']);
                    $studentData['carrera_id'] = $career->id;

                    $student = $user->estudiante()->updateOrCreate([], $studentData);

                    $student->formaciones()->updateOrCreate(
                        ['titulo' => $account['formation']['titulo']],
                        $account['formation']
                    );
                    $student->experiencias()->updateOrCreate(
                        [
                            'empresa' => $account['experience']['empresa'],
                            'cargo' => $account['experience']['cargo'],
                        ],
                        $account['experience']
                    );
                    $student->idiomas()->updateOrCreate(
                        ['idioma' => $account['language']['idioma']],
                        ['nivel' => $account['language']['nivel']]
                    );
                    $student->certificaciones()->updateOrCreate(
                        ['nombre' => $account['certification']['nombre']],
                        $account['certification']
                    );

                    $skills = [];
                    foreach ($account['skills'] as $skillData) {
                        $skill = Habilidad::firstOrCreate(
                            ['nombre' => $skillData['nombre']],
                            ['tipo' => $skillData['tipo']]
                        );
                        $skills[$skill->id] = ['nivel' => $skillData['nivel']];
                    }

                    $student->habilidades()->syncWithoutDetaching($skills);
                }

                $credentials[] = [
                    'Tipo' => $account['type'],
                    'Correo' => $account['email'],
                    'Contraseña' => $account['password'],
                ];
            }

            return $credentials;
        });

        $this->command->table(['Tipo', 'Correo', 'Contraseña'], $credentials);
    }
}
