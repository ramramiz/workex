<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
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
        .grand-total { font-size: 15px; font-weight: bold; color: #10b981; border-top: 1px solid #333; }
        .terms-box { margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 15px; page-break-inside: avoid; }
        .terms-title { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 5px; }
        .terms-content { font-size: 11px; color: #64748b; white-space: pre-wrap; }
        .scope-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 4px; margin-bottom: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <span style="font-size: 18px; font-weight: bold; color: #0f172a;">WorkeX</span><br>
                    info@company.com | +91-9999999999<br>
                    Your Company Address details
                </td>
                <td class="meta-text">
                    <span class="title">Proposal</span><br>
                    <strong># {{ $quotation->quotation_number }}</strong><br>
                    Date: {{ $quotation->created_at->format('d M Y') }}<br>
                    @if($quotation->valid_until)
                        <span style="color:#ef4444;">Valid Until: {{ $quotation->valid_until->format('d M Y') }}</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Addresses -->
        <div class="address-box">
            <table>
                <tr>
                    <td>
                        <strong style="color: #64748b; font-size: 10px; text-transform: uppercase;">Prepared For:</strong><br>
                        @if($quotation->client)
                            <strong style="font-size: 14px; color: #0f172a;">{{ $quotation->client->company_name }}</strong><br>
                            Attn: {{ $quotation->client->contact_person ?? '—' }}<br>
                            {{ $quotation->client->address }}<br>
                            {{ $quotation->client->city }}, {{ $quotation->client->state }} - {{ $quotation->client->pincode }}<br>
                            Email: {{ $quotation->client->email }}
                        @elseif($quotation->lead)
                            <strong style="font-size: 14px; color: #0f172a;">{{ $quotation->lead->client_name }}</strong><br>
                            Email: {{ $quotation->lead->client_email ?? '—' }}<br>
                            Phone: {{ $quotation->lead->client_phone ?? '—' }}
                        @else
                            <span style="color: #94a3b8;">No Client Attached</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Scope -->
        <div style="margin-top: 15px;">
            <strong style="font-size: 15px; color: #0f172a; display: block; margin-bottom: 8px;">{{ $quotation->title }}</strong>
            @if($quotation->scope)
                <div class="scope-box">{{ $quotation->scope }}</div>
            @endif
        </div>

        <!-- Items -->
        <table class="item-table">
            <thead>
                <tr class="heading">
                    <td>Scope / Module Description</td>
                    <td class="text-right" style="width: 150px;">Price (INR)</td>
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->modules ?? [] as $mod)
                    <tr class="item-row">
                        <td>{{ $mod['name'] ?? '' }}</td>
                        <td class="text-right">₹{{ number_format($mod['price'] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center; color: #94a3b8;">No modules added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals -->
        <div style="clear: both; overflow: hidden;">
            <table class="totals-table">
                <tr>
                    <td style="color:#64748b;">Subtotal:</td>
                    <td class="text-right">₹{{ number_format($quotation->subtotal ?? 0, 2) }}</td>
                </tr>
                @if($quotation->discount > 0)
                    <tr>
                        <td style="color:#64748b;">Discount:</td>
                        <td class="text-right" style="color: #ef4444;">- ₹{{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                @endif
                @if($quotation->tax > 0)
                    <tr>
                        <td style="color:#64748b;">GST Tax:</td>
                        <td class="text-right">₹{{ number_format($quotation->tax, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td style="padding-top: 8px;">Grand Total:</td>
                    <td class="text-right" style="padding-top: 8px;">₹{{ number_format($quotation->total ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Terms -->
        @if($quotation->terms)
            <div class="terms-box">
                <div class="terms-title">Terms & Conditions:</div>
                <div class="terms-content">{{ $quotation->terms }}</div>
            </div>
        @endif
    </div>
</body>
</html>
