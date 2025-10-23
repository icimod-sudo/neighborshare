<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeRequestNotification extends Notification implements ShouldQueue
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
            ->subject('New Exchange Request - ' . $this->exchange->product->title)
            ->line('You have a new exchange request for your product: ' . $this->exchange->product->title)
            ->line('From: ' . $this->exchange->fromUser->name)
            ->line('Message: ' . $this->exchange->message)
            ->action('View Request', route('exchanges.my'))
            ->line('Please respond within 24 hours.');
    }

    public function toArray($notifiable)
    {
        return [
            'exchange_id' => $this->exchange->id,
            'product_id' => $this->exchange->product_id,
            'product_title' => $this->exchange->product->title,
            'from_user_id' => $this->exchange->from_user_id,
            'from_user_name' => $this->exchange->fromUser->name,
            'message' => $this->exchange->message,
            'type' => 'exchange_request',
            'url' => route('exchanges.my')
        ];
    }
}
