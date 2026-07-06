<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Scholarship;

class ScholarshipParseFailed extends Notification implements ShouldQueue
{
    use Queueable;

    private Scholarship $scholarship;

    public function __construct(Scholarship $scholarship)
    {
        $this->scholarship = $scholarship;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('AI Parsing Failed for Scholarship #' . $this->scholarship->id)
            ->line('The AI eligibility parsing job failed after multiple attempts for the scholarship: "' . $this->scholarship->title . '".')
            ->action('Review Scholarship', route('admin.scholarships.edit', $this->scholarship->id))
            ->line('Please check the description and manually set the requirements.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'scholarship_parse_failed',
            'scholarship_id' => $this->scholarship->id,
            'title' => $this->scholarship->title,
        ];
    }
}