<?php

namespace App\Notifications;

use App\Models\Exchange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExchangeCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $exchange;
    public $cancelledBy;

    public function __construct(Exchange $exchange, $cancelledBy)
    {
        $this->exchange = $exchange;
        $this->cancelledBy = $cancelledBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $cancellerName = $this->cancelledBy === 'requester'
            ? $this->exchange->fromUser->name
            : $this->exchange->toUser->name;

        return (new MailMessage)
            ->subject('Exchange Cancelled - ' . $this->exchange->product->title)
            ->line('The exchange for "' . $this->exchange->product->title . '" has been cancelled by ' . $cancellerName . '.')
            ->action('View Details', route('exchanges.my'));
    }

    public function toArray($notifiable)
    {
        return [
            'exchange_id' => $this->exchange->id,
            'product_id' => $this->exchange->product_id,
            'product_title' => $this->exchange->product->title,
            'cancelled_by' => $this->cancelledBy,
            'cancelled_by_name' => $this->cancelledBy === 'requester'
                ? $this->exchange->fromUser->name
                : $this->exchange->toUser->name,
            'type' => 'exchange_cancelled',
            'url' => route('exchanges.my')
        ];
    }
}
