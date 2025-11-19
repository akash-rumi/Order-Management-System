<h3>Order Status Updated — {{ $order->order_number }}</h3>
<p>Hi {{ $order->user->name }},</p>
<p>The status for your order <strong>{{ $order->order_number }}</strong> changed from <strong>{{ $oldStatus }}</strong> to <strong>{{ $newStatus }}</strong>.</p>
<p>Track your order in your account.</p>
