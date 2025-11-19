<?php
namespace App\Listeners;

use App\Events\LowStockAlertCreated;
use App\Mail\LowStockAlertMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;


class SendLowStockAlertEmail
{
    public function handle(LowStockAlertCreated $event)
    {
        $alert = $event->alert;

        // determine recipients — simple approach: vendor + admin emails
        $variant = $alert->variant;
        $product = $variant->product;
        $vendor = $product->vendor;

        $emails = [];
        if ($vendor && $vendor->email) $emails[] = $vendor->email;

        // add admin(s)
        $admins = User::role('admin')->pluck('email')->filter()->toArray();
        $emails = array_merge($emails, $admins);

        foreach (array_unique($emails) as $email) {
            Mail::to($email)->queue(new LowStockAlertMail($alert->id));
        }
    }
}
