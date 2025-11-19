<?php
namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Jobs\GenerateInvoiceJob;

class GenerateInvoiceAndSendEmail
{
    public function handle(OrderConfirmed $event)
    {
        // Dispatch job that generates PDF and emails the customer
        GenerateInvoiceJob::dispatch($event->order->id);
    }
}
