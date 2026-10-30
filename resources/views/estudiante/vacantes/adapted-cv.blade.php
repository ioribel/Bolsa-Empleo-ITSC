<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Currículum adaptado</title>
    <style>
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.5; }
        h1 { color: #042c53; font-size: 22pt; margin-bottom: 2pt; }
        h2 { border-bottom: 1px solid #d1d5db; color: #042c53; font-size: 13pt; margin: 18pt 0 6pt; padding-bottom: 3pt; }
        p { margin: 4pt 0; }
        .meta { color: #4b5563; font-size: 9pt; }
        .notice { border-top: 1px solid #d1d5db; color: #6b7280; font-size: 8pt; margin-top: 24pt; padding-top: 8pt; }
    </style>
</head>
<body>
    <h1>{{ $student->user->name }}</h1>
    <p class="meta">{{ $student->user->email }} · {{ $student->carrera->nombre }}</p>

    @if ($adaptedCv['profile']['text'] !== '')
        <h2>Perfil profesional</h2>
        <p>{{ $adaptedCv['profile']['text'] }}</p>
    @endif

    @php
        $sectionLabels = [
            'experience' => 'Experiencia laboral',
            'education' => 'Formación académica',
            'projects' => 'Proyectos',
            'skills' => 'Habilidades',
            'technologies' => 'Tecnologías y herramientas',
            'certifications' => 'Certificaciones',
            'languages' => 'Idiomas',
        ];
    @endphp
    @foreach ($sectionLabels as $section => $label)
        @if ($adaptedCv[$section])
            <h2>{{ $label }}</h2>
            @foreach ($adaptedCv[$section] as $item)
                <p>• {{ $item['text'] }}</p>
            @endforeach
        @endif
    @endforeach

    @php
        $hasStudentConfirmedContent = $adaptedCv['profile']['text'] !== '' && ! $adaptedCv['profile']['verified'];
        foreach (array_keys($sectionLabels) as $section) {
            foreach ($adaptedCv[$section] as $item) {
                if ($item['student_confirmed']) {
                    $hasStudentConfirmedContent = true;
                    break 2;
                }
            }
        }
    @endphp
    @if ($hasStudentConfirmedContent)
        <p class="notice">El estudiante confirmó como verdadero el contenido agregado o editado que no tiene cita verificable en el CV o en el perfil.</p>
    @endif

    <p class="notice">Versión revisada por el estudiante para la vacante “{{ $vacante->titulo }}” en {{ $vacante->empresa->nombre_empresa }}. Verifica que el contenido refleje tus antecedentes reales.</p>
</body>
</html>
