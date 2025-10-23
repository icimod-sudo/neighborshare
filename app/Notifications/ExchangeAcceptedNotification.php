<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Exchange Request Accepted - ' . $this->exchange->product->title)
            ->line('Your exchange request for "' . $this->exchange->product->title . '" has been accepted!')
            ->line('Please contact the seller to coordinate the exchange:')
            ->line('Contact: ' . $this->exchange->product->user->email)
            ->action('View Details', route('exchanges.show', $this->exchange))
            ->line('Please complete the exchange within 24 hours.');
    }

    public function toArray($notifiable)
    {
        return [
            'exchange_id' => $this->exchange->id,
            'product_id' => $this->exchange->product_id,
            'product_title' => $this->exchange->product->title,
            'to_user_id' => $this->exchange->to_user_id,
            'to_user_name' => $this->exchange->toUser->name,
            'type' => 'exchange_accepted',
            'url' => route('exchanges.show', $this->exchange)
        ];
    }
}
