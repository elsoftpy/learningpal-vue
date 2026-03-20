<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $hora,
        public string $url,
        public string $greeting,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->greeting($this->greeting)
            ->line("Te recordamos que tu clase de hoy comienza a las {$this->hora}.")
            ->line('Puedes acceder a ella haciendo click en el boton de abajo.')
            ->action('Ir a clase', $this->url)
            ->line('Recuerda:')
            ->line('Tambien puedes acceder ingresando a la plataforma https://academy.ipl.com.py')
            ->line('Tus datos de usuario y contrasena te los enviamos por mensaje cuando los creamos. Suelen ser tu nombre y la primera letra de tu apellido. Ejemplo: NombreA y contrasena numero de cedula sin puntos.')
            ->line('Tus actividades a distancia tambien se encuentran en la plataforma y puedes revisarlas cuando quieras.');

        $ccAddress = config('services.class_notification.cc');

        if ($ccAddress) {
            $mailMessage->cc($ccAddress);
        }

        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
