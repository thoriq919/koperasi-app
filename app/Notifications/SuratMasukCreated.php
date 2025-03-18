<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratMasukCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $suratMasuk;

    public function __construct($suratMasuk)
    {
        //
        $this->suratMasuk = $suratMasuk;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Surat masuk baru dengan nomor {$this->suratMasuk->nomor_surat} telah dibuat.",
            'nomor_surat' => $this->suratMasuk->nomor_surat,
            'pengirim' => $this->suratMasuk->pengirim,
            'perihal' => $this->suratMasuk->perihal,
            'tanggal_masuk' => $this->suratMasuk->tanggal_masuk->format('d M Y'),
            'status' => $this->suratMasuk->status,
            'format' => 'filament', 
        ];
    }
}
