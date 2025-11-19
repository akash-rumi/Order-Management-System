<?php
namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusChangedEmail
{
    public function handle(OrderStatusChanged $event)
    {
        // Send email to customer asynchronously
        Mail::to($event->order->user->email)
            ->queue(new OrderStatusChangedMail($event->order->id, $event->oldStatus, $event->newStatus));
    }
}
