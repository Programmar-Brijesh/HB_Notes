<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminMagicLoginNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $plainToken,
        public int $expiryMinutes = 10
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('login.magic.show', ['token' => $this->plainToken]);

        return (new MailMessage)
            ->subject('HB Notes Admin — Secure Sign-In Link')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('You recently requested a secure, passwordless sign-in link for the HB Notes Administration Portal.')
            ->action('Sign In to Admin Portal', $url)
            ->line('This single-use link will expire in ' . $this->expiryMinutes . ' minutes.')
            ->line('Security Notice: To protect your account from automated scanners, you will be prompted on a confirmation screen before entering the dashboard.')
            ->line('If you did not request this link, please disregard this email or notify your system administrator immediately.');
    }
}
