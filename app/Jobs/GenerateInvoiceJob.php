<?php
namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderConfirmedMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateInvoiceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    protected int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $order = Order::with('items')->find($this->orderId);
        if (!$order) return;

        // Render invoice blade view to PDF
        $pdf = Pdf::loadView('invoices.pdf', ['order' => $order]);

        // Ensure invoices directory exists and use order_number as filename
        $filename = 'invoices/' . $order->order_number . '.pdf';
        Storage::put($filename, $pdf->output());

        // Save pdf path to invoice record (create or update)
        $invoice = $order->invoice ?? null;
        if ($invoice) {
            $invoice->pdf_path = $filename;
            $invoice->status = 'created';
            $invoice->save();
        } else {
            // create invoice record
            $order->invoice()->create([
                'pdf_path' => $filename,
                'status' => 'created'
            ]);
        }

        // Send email to customer with invoice attached (Mailable handles attach)
        Mail::to($order->user->email)->queue(new OrderConfirmedMail($order->id, $filename));
    }
}
