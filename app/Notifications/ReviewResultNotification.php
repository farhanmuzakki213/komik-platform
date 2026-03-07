<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewResultNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $title;
    protected $status;
    protected $notes;
    /**
     * Create a new notification instance.
     */
    public function __construct($type, $title, $status, $notes = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $statusText = $this->status === 'approved' ? 'disetujui dan diterbitkan' : 'ditolak';
        $message = "Karya {$this->type} Anda '{$this->title}' telah {$statusText}.";

        if ($this->notes) {
            $message .= " Catatan Admin: " . $this->notes;
        }

        return [
            'title' => 'Hasil Review ' . $this->type,
            'message' => $message,
            'url' => route('comics.index'),
            'icon' => $this->status === 'approved' ? 'check' : 'x',
            'status' => $this->status
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
