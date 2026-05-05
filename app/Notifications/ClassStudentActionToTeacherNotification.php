<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassStudentActionToTeacherNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $teacherName,
        public string $studentName,
        public string $sessionDate,
        public string $startTime,
        public string $courseName,
        public string $actionType,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionMessage = match ($this->actionType) {
            'pending' => "quedará pendiente de reprogramación por indicación del estudiante {$this->studentName}.",
            default => "se ha cancelado por indicación del estudiante {$this->studentName} quien solicita subir el class record a la plataforma.",
        };

        return (new MailMessage)
            ->subject(__('Student action notification'))
            ->markdown('emails.class-student-action-to-teacher', [
                'teacherName' => $this->teacherName,
                'sessionDate' => $this->sessionDate,
                'startTime' => $this->startTime,
                'courseName' => $this->courseName,
                'actionMessage' => $actionMessage,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
