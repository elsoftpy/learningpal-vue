<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar solicitud</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }

        .card {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 24px;
        }

        p {
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            background: #0d6efd;
            color: #ffffff;
            padding: 12px 18px;
            font-size: 16px;
            cursor: pointer;
        }

        .note {
            color: #6b7280;
            font-size: 14px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Confirma tu solicitud</h1>

    @if ($actionType === 'pending')
        <p>Vas a solicitar dejar pendiente esta clase para su reprogramación.</p>
        <button form="action-form" class="button" type="submit">Confirmar dejar pendiente</button>
    @else
        <p>Vas a solicitar cancelar esta clase para subir la tarea/class record a la plataforma.</p>
        <button form="action-form" class="button" type="submit">Confirmar subir tarea</button>
    @endif

    <form id="action-form" method="POST" action="{{ $executeUrl }}">
        @csrf
    </form>

    <p class="note">Esta pantalla evita ejecuciones automáticas de seguridad en correos y requiere tu confirmación manual.</p>
</div>
</body>
</html>
