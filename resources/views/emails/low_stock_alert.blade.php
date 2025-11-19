<h3>Low Stock Alert</h3>
<p>Product: {{ $alert->variant->product->name ?? 'N/A' }}</p>
<p>Variant SKU: {{ $alert->variant->sku ?? $alert->variant->id }}</p>
<p>Available: {{ $alert->inventory_after }}</p>
<p>Threshold: {{ $alert->variant->inventory->low_stock_threshold ?? 'N/A' }}</p>
<p>Please restock as soon as possible.</p>
