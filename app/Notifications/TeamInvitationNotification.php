<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TeamInvitation $invitation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;
        $role = $this->invitation->role === 'admin' ? __('Admin') : __('Member');

        return (new MailMessage)
            ->subject(__('You have been invited to join :team', ['team' => $team->name]))
            ->greeting(__('Hello!'))
            ->line(__('You have been invited to join the team **:team** as a **:role**.', [
                'team' => $team->name,
                'role' => $role,
            ]))
            ->action(__('Accept Invitation'), $this->invitation->getAcceptUrl())
            ->line(__('This invitation will expire :date.', [
                'date' => $this->invitation->expires_at->diffForHumans(),
            ]))
            ->line(__('If you did not expect this invitation, you can ignore this email.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'team_id' => $this->invitation->team_id,
            'invitation_id' => $this->invitation->id,
        ];
    }
}
