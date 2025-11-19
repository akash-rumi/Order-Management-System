<?php
namespace App\Mail;

use App\Models\LowStockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use App\Models\User;


class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public int $lowStockAlertId)
    {
        // 
    }

    public function envelope(): Envelope
    {
        $alert = $this->getAlert();
        $admins = User::where('role', 'admin')->orWhere('role', 'inventory')->get();

        $to = $admins->map(fn($user) => new Address($user->email, $user->name))->toArray();

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: $to, // Use admin email as recipient
            subject: "Low Stock Alert – {$alert->variant->product->name} (Variant #{$alert->variant->id})",
        );
    }

    public function build()
    {
        $alert = $this->getAlert();
        return $this->view('emails.low_stock_alert')
                    ->with(['alert' => $alert]);
    }

    protected function getAlert(): LowStockAlert
    {
        return LowStockAlert::with(['variant.product'])
                            ->findOrFail($this->lowStockAlertId);
    }
}
