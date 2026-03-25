<?php

namespace App\Mail;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginSecurityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public ActivityLog $activity;
    public string $userName;
    public string $userEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(ActivityLog $activity)
    {
        $this->activity = $activity;
        $this->userName = $activity->user->name;
        $this->userEmail = $activity->user->email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.login_notification_subject', ['app_name' => config('app.name')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.login-security-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
