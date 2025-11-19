<h3>Order Confirmed — {{ $order->order_number }}</h3>
<p>Hi {{ $order->user->name }},</p>
<p>Your order <strong>{{ $order->order_number }}</strong> has been confirmed. Attached is your invoice.</p>
<p>Thank you for ordering!</p>
