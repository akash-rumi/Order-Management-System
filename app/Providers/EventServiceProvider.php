<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderConfirmed;
use App\Listeners\GenerateInvoiceAndSendEmail;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusChangedEmail;
use App\Events\LowStockAlertCreated;
use App\Listeners\SendLowStockAlertEmail;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderConfirmed::class => [
            GenerateInvoiceAndSendEmail::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedEmail::class,
        ],
        LowStockAlertCreated::class => [
            SendLowStockAlertEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
