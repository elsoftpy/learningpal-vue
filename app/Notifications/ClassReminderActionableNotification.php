<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassReminderActionableNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $hora,
        public string $classUrl,
        public string $greeting,
        public string $notifyUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject(__('Class reminder'))
            ->markdown('emails.class-reminder-actionable', [
                'greeting' => $this->greeting,
                'hora' => $this->hora,
                'classUrl' => $this->classUrl,
                'notifyUrl' => $this->notifyUrl,
            ]);

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
