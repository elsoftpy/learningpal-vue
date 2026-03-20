@component('mail::message')
{{ $greeting }}

Te recordamos que tu clase de hoy comienza a las {{ $hora }}.

Puedes acceder a ella haciendo click en el boton de abajo.

@component('mail::button', ['url' => $classUrl])
Ir a clase
@endcomponent

En caso de que no puedas asistir a la sala virtual por favor avisanos haciendo click en el botón de abajo.

@component('mail::button', ['url' => $notifyUrl])
Avisar
@endcomponent

Recuerda:

Tambien puedes acceder ingresando a la plataforma https://academy.ipl.com.py

Tus datos de usuario y contrasena te los enviamos por mensaje cuando los creamos. Suelen ser tu nombre y la primera letra de tu apellido. Ejemplo: NombreA y contrasena numero de cedula sin puntos.

Tus actividades a distancia tambien se encuentran en la plataforma y puedes revisarlas cuando quieras.
@endcomponent
