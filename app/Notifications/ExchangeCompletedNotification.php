<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $exchange;
    public $userType;

    public function __construct(Exchange $exchange, $userType)
    {
        $this->exchange = $exchange;
        $this->userType = $userType;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->userType === 'requester') {
            return (new MailMessage)
                ->subject('Exchange Completed Successfully')
                ->line('Your exchange for "' . $this->exchange->product->title . '" has been marked as completed.')
                ->line('Thank you for using NeighborShare!')
                ->action('View Exchange', route('exchanges.show', $this->exchange));
        } else {
            return (new MailMessage)
                ->subject('Exchange Completed Successfully')
                ->line('Your exchange for "' . $this->exchange->product->title . '" has been marked as completed.')
                ->line('Thank you for sharing with your community!')
                ->action('View Exchange', route('exchanges.show', $this->exchange));
        }
    }

    public function toArray($notifiable)
    {
        return [
            'exchange_id' => $this->exchange->id,
            'product_id' => $this->exchange->product_id,
            'product_title' => $this->exchange->product->title,
            'user_type' => $this->userType,
            'type' => 'exchange_completed',
            'url' => route('exchanges.show', $this->exchange)
        ];
    }
}
