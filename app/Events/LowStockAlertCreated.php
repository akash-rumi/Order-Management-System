<?php
namespace App\Events;

use App\Models\LowStockAlert;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlertCreated
{
    use Dispatchable, SerializesModels;

    public LowStockAlert $alert;

    public function __construct(LowStockAlert $alert)
    {
        $this->alert = $alert;
    }
}
