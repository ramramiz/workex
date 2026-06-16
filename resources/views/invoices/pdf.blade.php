<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 13px; line-height: 1.4; margin: 0; padding: 0; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 8px; vertical-align: top; }
        .header-table td { padding: 0 0 20px 0; }
        .title { font-size: 28px; font-weight: bold; color: #6366f1; text-transform: uppercase; }
        .meta-text { text-align: right; }
        .address-box { margin-bottom: 25px; }
        .address-box table td { padding: 0; width: 50%; }
        .heading { background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: bold; }
        .item-table { margin-bottom: 25px; }
        .item-table td { border-bottom: 1px solid #f1f5f9; }
        .item-table .item-row td { padding: 12px 8px; }
        .text-right { text-align: right; }
        .totals-table { float: right; width: 280px; margin-top: 10px; }
        .totals-table td { padding: 4px 8px; }
        .grand-total { font-size: 15px; font-weight: bold; color: #0f172a; border-top: 1px solid #e2e8f0; }
        .balance-row { font-size: 16px; font-weight: bold; color: #ef4444; border-top: 1px solid #333; }
        .terms-box { margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 15px; page-break-inside: avoid; }
        .terms-title { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 5px; }
        .terms-content { font-size: 11px; color: #64748b; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <span style="font-size: 18px; font-weight: bold; color: #0f172a;">WorkeX</span><br>
                    billing@company.com | +91-9999999999<br>
                    Your Company Address details
                </td>
                <td class="meta-text">
                    <span class="title">Invoice</span><br>
                    <strong># {{ $invoice->invoice_number }}</strong><br>
                    Date: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '—' }}<br>
                    <span style="color:#ef4444;">Due Date: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</span>
                </td>
            </tr>
        </table>

        <!-- Addresses -->
        <div class="address-box">
            <table>
                <tr>
                    <td>
                        <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">Billed To:</strong><br>
                        @if($invoice->client)
                            <strong style="font-size: 14px; color: #0f172a;">{{ $invoice->client->company_name }}</strong><br>
                            Attn: {{ $invoice->client->contact_person ?? '—' }}<br>
                            {{ $invoice->client->address }}<br>
                            {{ $invoice->client->city }}, {{ $invoice->client->state }} - {{ $invoice->client->pincode }}<br>
                            Email: {{ $invoice->client->email }}
                        @else
                            <span style="color: #94a3b8;">No Client Linked</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items -->
        <table class="item-table">
            <thead>
                <tr class="heading">
                    <td>Item Description</td>
                    <td style="width: 60px; text-align: center;">Qty</td>
                    <td class="text-right" style="width: 130px;">Unit Rate</td>
                    <td class="text-right" style="width: 140px;">Amount (INR)</td>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items ?? [] as $item)
                    @php
                        $qty = floatval($item['qty'] ?? 1);
                        $price = floatval($item['price'] ?? 0);
                        $rowTotal = $qty * $price;
                    @endphp
                    <tr class="item-row">
                        <td>{{ $item['name'] ?? '' }}</td>
                        <td style="text-align: center;">{{ $qty }}</td>
                        <td class="text-right">₹{{ number_format($price, 2) }}</td>
                        <td class="text-right">₹{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8;">No items billed.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals -->
        <div style="clear: both; overflow: hidden;">
            <table class="totals-table">
                <tr>
                    <td style="color:#64748b;">Subtotal:</td>
                    <td class="text-right">₹{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                </tr>
                @if($invoice->discount > 0)
                    <tr>
                        <td style="color:#64748b;">Discount:</td>
                        <td class="text-right" style="color: #ef4444;">- ₹{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->tax_amount > 0)
                    <tr>
                        <td style="color:#64748b;">GST Tax ({{ $invoice->tax_percentage ?? 18 }}%):</td>
                        <td class="text-right">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td style="padding-top: 8px;">Grand Total:</td>
                    <td class="text-right" style="padding-top: 8px;">₹{{ number_format($invoice->total ?? 0, 2) }}</td>
                </tr>
                <tr style="color: #10b981;">
                    <td>Amount Paid:</td>
                    <td class="text-right">₹{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                </tr>
                <tr class="balance-row">
                    <td style="padding-top: 8px;">Balance Due:</td>
                    <td class="text-right" style="padding-top: 8px;">₹{{ number_format($invoice->balance_amount ?? $invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Terms -->
        @if($invoice->notes)
            <div class="terms-box">
                <div class="terms-title">Payment details & Instructions:</div>
                <div class="terms-content">{{ $invoice->notes }}</div>
            </div>
        @endif
    </div>
</body>
</html>
