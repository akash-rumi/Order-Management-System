<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Storage;


class OrderConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public int $orderId,  public ?string $invoicePath = null)
    {
        // 
    }

    public function envelope(): Envelope
    {
        $order = Order::with(['user', 'items', 'invoice'])->findOrFail($this->orderId);

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')), // <-- THIS IS THE FIX
            to: [new Address($order->user->email, $order->user->name ?? null)],
            subject: "Your order {$order->order_number} is confirmed",
        );
    }

    public function build()
    {
        $order = Order::with(['user', 'items', 'invoice'])->findOrFail($this->orderId);
        $mail = $this->view('emails.order_confirmed')
                     ->with(['order' => $order]);

        if ($this->invoicePath) {
            if (\Storage::exists($this->invoicePath)) {
                $mail->attach(\Storage::path($this->invoicePath), [
                    'as'   => $order->order_number . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
