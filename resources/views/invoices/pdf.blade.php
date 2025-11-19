<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Invoice - {{ $order->order_number }}</title>
        <style>
            body
            {
                font-family: DejaVu Sans,
                sans-serif;
                font-size: 12px;
            }
            .header
            {
                text-align: center;
                margin-bottom: 20px;
            }
            table
            {
                width: 100%;
                border-collapse: collapse;
            }
            th, td
            {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .right
            {
                text-align: right;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>Invoice</h2>
            <div>Order: {{ $order->order_number }}</div>
            <div>Date: {{ $order->created_at->format('Y-m-d') }}</div>
        </div>

        <h4>Customer</h4>
        <div>{{ $order->shipping_address['name'] ?? $order->user->name }}</div>
        <div>{{ $order->shipping_address['line1'] ?? '' }}</div>
        <div>{{ $order->shipping_address['city'] ?? '' }} {{ $order->shipping_address['country'] ?? '' }}</div>

        <h4>Items</h4>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Attributes</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_snapshot['sku'] ?? '' }}</td>
                        <td>{{ $item->product_snapshot['product_name'] ?? '' }}</td>
                        <td>
                            @if(!empty($item->product_snapshot['attributes']))
                                @foreach($item->product_snapshot['attributes'] as $k=>$v)
                                    {{ $k }}: {{ $v }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="right">{{ number_format($item->unit_price,2) }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ number_format($item->line_total,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>{{ number_format($order->total_amount,2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
