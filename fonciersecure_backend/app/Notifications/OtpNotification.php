<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('FoncierSecure - Code de réinitialisation')
            ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom . ',')
            ->line('Vous avez demandé la réinitialisation de votre mot de passe.')
            ->line('Votre code de vérification est :')
            ->line('**' . $this->otp . '**')
            ->line('Ce code expire dans 15 minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.');
    }
}
