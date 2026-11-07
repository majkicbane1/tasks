<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent when the platform admin creates a firm / admin account for someone. */
class LoginDetailsNotification extends Notification
{
    public function __construct(
        private string $firm,
        private string $password,
        private bool $temporary,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Podaci za prijavu - uizzard')
            ->greeting('Zdravo '.$notifiable->name.',')
            ->line("Napravljen je nalog za firmu {$this->firm}.")
            ->line('Email: '.$notifiable->email)
            ->line('Lozinka: '.$this->password);

        if ($this->temporary) {
            $mail->line('**Ovo je jednokratna lozinka, važi samo za prvu prijavu.** Odmah posle prijave bićeš zamoljen da postaviš svoju lozinku.');
        }

        return $mail->action('Prijavi se', route('login'));
    }
}
