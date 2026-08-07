<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #1f2937; }
        .header { display: table; width: 100%; margin-bottom: 20px; }
        .header .brand { display: table-cell; width: 50%; vertical-align: top; }
        .header .meta { display: table-cell; width: 50%; vertical-align: top; text-align: right; }
        .muted { color: #6b7280; }
        .logo { height: 34px; margin-bottom: 8px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th, table.items td { border: 1px solid #d1d5db; padding: 6px 8px; font-size: 11px; text-align: left; }
        table.items th { background-color: #f3fbfc; color: #245a5b; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 40%; margin-left: 60%; margin-top: 10px; border-collapse: collapse; }
        table.totals td { padding: 4px 8px; font-size: 12px; }
        table.totals td.label { color: #245a5b; }
        table.totals tr.grand-total td { border-top: 1px solid #245a5b; font-weight: bold; font-size: 13px; }
        .addresses { display: table; width: 100%; margin-top: 20px; }
        .addresses .col { display: table-cell; width: 50%; vertical-align: top; }
        .footer { margin-top: 40px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <img class="logo" src="{{ public_path('images/nature-care-logo.jpeg') }}" alt="Nature Care Products">
            <p class="muted">{{ $sellerAddress }}</p>
            @if ($gstin)
                <p class="muted">GSTIN: {{ $gstin }}</p>
            @endif
        </div>
        <div class="meta">
            <p><strong>Tax Invoice</strong></p>
            <p>Invoice No: {{ $invoiceNumber }}</p>
            <p>Order No: {{ $order->order_number }}</p>
            <p>Date: {{ $order->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="addresses">
        <div class="col">
            <p class="muted">Billed / Shipped To</p>
            <p>
                <strong>{{ $order->contact_name }}</strong><br>
                {{ $order->shipping_address['line1'] ?? '' }}<br>
                @if (! empty($order->shipping_address['line2']))
                    {{ $order->shipping_address['line2'] }}<br>
                @endif
                {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}<br>
                Mobile: {{ $order->contact_mobile }}
            </p>
        </div>
        <div class="col">
            <p class="muted">Payment</p>
            <p>
                Method: {{ $order->payment_method->label() }}<br>
                Status: {{ $order->payment_status->label() }}<br>
                Tax Type: {{ $order->isInterState() ? 'IGST (Inter-state)' : 'CGST + SGST (Intra-state)' }}
            </p>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>HSN</th>
                <th>Size</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_name_snapshot }}</td>
                    <td>{{ $item->hsn_code_snapshot ?: '-' }}</td>
                    <td>{{ $item->size_label_snapshot }}</td>
                    <td class="num">{{ $item->qty }}</td>
                    <td class="num">{{ $item->unit_price->format() }}</td>
                    <td class="num">{{ $item->line_total->format() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal</td><td class="num">{{ $order->subtotal->format() }}</td></tr>
        @if (! $order->discount_total->isZero())
            <tr><td class="label">Discount{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</td><td class="num">-{{ $order->discount_total->format() }}</td></tr>
        @endif
        <tr><td class="label">Shipping</td><td class="num">{{ $order->shipping_charge->format() }}</td></tr>
        @if ($order->isInterState())
            <tr><td class="label">IGST</td><td class="num">{{ $order->tax_igst->format() }}</td></tr>
        @else
            <tr><td class="label">CGST</td><td class="num">{{ $order->tax_cgst->format() }}</td></tr>
            <tr><td class="label">SGST</td><td class="num">{{ $order->tax_sgst->format() }}</td></tr>
        @endif
        <tr class="grand-total"><td class="label">Total</td><td class="num">{{ $order->total->format() }}</td></tr>
    </table>

    <div class="footer">
        This is a computer-generated invoice from Nature Care Products and does not require a signature.
    </div>
</body>
</html>
