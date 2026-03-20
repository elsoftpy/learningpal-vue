@component('mail::message')
!Hola {{ $teacherName }}!

Te informamos que la clase de las {{ $startTime }} del curso {{ $courseName }} {{ $actionMessage }}

Saludos cordiales,

Team IPL.
@endcomponent
