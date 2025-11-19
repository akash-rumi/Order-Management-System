<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;


class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public int $orderId, public string $oldStatus, public string $newStatus)
    {
        // 
    }

    public function envelope(): Envelope
    {
        $order = $this->getOrder();

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: [new Address($order->user->email, $order->user->name ?? null)],
            subject: "Your order {$order->order_number} is now {$this->newStatus}",
        );
    }
    
    public function build()
    {
        $order = $this->getOrder();

        return $this->view('emails.order_status_changed')
                    ->with([
                        'order' => $order,
                        'oldStatus' => $this->oldStatus,
                        'newStatus' => $this->newStatus,
                    ]);
    }

    /**
     * Load the order fresh from database with needed relations
     */
    protected function getOrder(): Order
    {
        return Order::with(['user', 'items']) // add any other relations you use in the email
                    ->findOrFail($this->orderId);
    }
}
