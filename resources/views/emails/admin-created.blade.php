<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu cuenta de administrador está lista</title>
</head>
<body style="margin:0;background:#f4f6f8;color:#1b242c;font-family:Arial,sans-serif;line-height:1.6">
    <main style="max-width:560px;margin:32px auto;padding:32px;background:#fff;border:1px solid #e3e8ed;border-radius:12px">
        <p style="margin:0;color:#0a3d70;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Bolsa de Empleo ITSC</p>
        <h1 style="margin:16px 0;font-size:24px">Hola {{ $name }}, tu cuenta está lista</h1>
        <p>Se creó una cuenta de administrador para ti. Tu correo de acceso es <strong>{{ $email }}</strong>.</p>
        <p>Por seguridad, define tu contraseña con el siguiente enlace. Este enlace es personal y de un solo uso.</p>
        <p style="margin:28px 0">
            <a href="{{ $passwordUrl }}" target="_blank" rel="noopener noreferrer"
                style="display:inline-block;padding:12px 20px;border-radius:8px;background:#2e3b47;color:#fff;text-decoration:none;font-weight:700">
                Cambiar contraseña
            </a>
        </p>
        <p>Luego podrás iniciar sesión desde la <a href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer">página de acceso</a>.</p>
        <p style="margin-top:28px;color:#6b7280;font-size:13px">Si no esperabas esta invitación, puedes ignorar este mensaje.</p>
    </main>
</body>
</html>
