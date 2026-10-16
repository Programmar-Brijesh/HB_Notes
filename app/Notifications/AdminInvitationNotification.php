<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $plainToken,
        public string $inviterName,
        public int $expiryHours = 48
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.invitations.show', ['token' => $this->plainToken]);

        return (new MailMessage)
            ->subject('HB Notes — Administrator Portal Invitation')
            ->greeting('Welcome to HB Notes!')
            ->line($this->inviterName . ' has invited you to join the HB Notes Administration team.')
            ->line('As an administrator, you will manage educational notes, curriculum resources, and academic content for Classes 9–12.')
            ->action('Accept Invitation & Set Up Account', $url)
            ->line('This invitation link is valid for ' . $this->expiryHours . ' hours and can only be used once.')
            ->line('If you were not expecting this invitation, you may safely ignore this message.');
    }
}
