<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Team;
use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TeamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Team $team,
        public TeamInvitationModel $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You've been invited to join {$this->team->name}")
            ->line("You have been invited to join the team {$this->team->name}.")
            ->action('Accept Invitation', route('teams.invitations.accept', ['team' => $this->team->id, 'invitation' => $this->invitation->uuid]))
            ->line('If you did not expect to receive this invitation, you can ignore this email.');
    }
}
