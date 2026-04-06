<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Receipt – {{ $group->group_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #2d2d2d;
            background: #fff;
            padding: 30px;
        }

        /* ── Outer wrapper ───────────────────────────────────────────────────── */
        .receipt-wrapper {
            border: 1.5px solid #2563b0;
            border-radius: 4px;
            overflow: hidden;
        }

        /* ── Top accent bar ──────────────────────────────────────────────────── */
        .top-bar {
            background: #1a3a6b;
            height: 8px;
        }

        /* ── Logo / brand header ─────────────────────────────────────────────── */
        .brand-header {
            text-align: center;
            padding: 12px 20px 10px;
            border-bottom: 1px solid #d0daf0;
        }
        .brand img {
            height: 48px;
            width: auto;
        }

        /* ── Meta row (customer | receipt info) ──────────────────────────────── */
        .meta-row {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #d0daf0;
        }
        .meta-row td {
            padding: 12px 20px;
            vertical-align: top;
        }
        .meta-row td:last-child {
            text-align: right;
            border-left: 1px solid #d0daf0;
        }
        .meta-label {
            font-size: 10px;
            color: #777;
            margin-bottom: 2px;
        }
        .meta-value {
            font-size: 11px;
            color: #1a1a1a;
            font-weight: bold;
        }
        .meta-value.small {
            font-weight: normal;
            font-size: 10.5px;
        }
        .receipt-no {
            font-size: 12px;
            font-weight: bold;
            color: #1a3a6b;
            letter-spacing: 1px;
        }

        /* ── Section body padding ────────────────────────────────────────────── */
        .section-body { padding: 0 20px 16px; }

        /* ── Section title ───────────────────────────────────────────────────── */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a3a6b;
            border-bottom: 1.5px solid #2563b0;
            padding: 10px 0 6px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ── Order details table ─────────────────────────────────────────────── */
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-table thead tr {
            background: #1a3a6b;
            color: #fff;
        }
        .order-table thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.4px;
        }
        .order-table thead th.right { text-align: right; }
        .order-table tbody tr {
            border-bottom: 1px solid #eaecf5;
        }
        .order-table tbody td {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 11px;
        }
        .order-table tbody td.right { text-align: right; }
        .order-table tbody tr:last-child { border-bottom: none; }

        /* ── Pricing summary ─────────────────────────────────────────────────── */
        .summary-wrap {
            padding: 0 20px 20px;
        }
        .summary-table {
            width: 55%;
            margin-left: auto;
            border-collapse: collapse;
            border: 1px solid #d0daf0;
            border-radius: 3px;
        }
        .summary-table td {
            padding: 6px 12px;
            font-size: 11px;
            border-bottom: 1px solid #eaecf5;
        }
        .summary-table td.sl { color: #555; }
        .summary-table td.sr { text-align: right; font-weight: 500; }
        .summary-table tr:last-child td { border-bottom: none; }
        .summary-table .total-row td {
            background: #1a3a6b;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
        }
        .discount-val { color: #c0392b; }

        /* ── Footer ──────────────────────────────────────────────────────────── */
        .receipt-footer {
            background: #f4f6fb;
            border-top: 1px solid #d0daf0;
            text-align: center;
            padding: 8px 20px;
            font-size: 9.5px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="receipt-wrapper">

    {{-- ── Top accent bar ──────────────────────────────────────────────────────── --}}
    <div class="top-bar"></div>

    {{-- ── Brand header ────────────────────────────────────────────────────────── --}}
    <div class="brand-header">
        <div class="brand">
            <img src="{{ public_path('admin/images/logo.png') }}" alt="XPark">
        </div>
    </div>

    {{-- ── Meta row: customer (left) | receipt info (right) ───────────────────── --}}
    @php $firstItem = $group->courtBookings->first(); @endphp
    <table class="meta-row">
        <tr>
            <td style="width:55%;">
                <div class="meta-label">Customer Name</div>
                <div class="meta-value">{{ $group->user?->name ?? '–' }}</div>

                <div class="meta-label" style="margin-top:8px;">Customer Address</div>
                <div class="meta-value small">
                    @php $venue = $firstItem?->court?->venueSport?->venue; @endphp
                    @if($venue)
                        {{ $venue->address_1 }}@if($venue->address_2), {{ $venue->address_2 }}@endif<br>
                        {{ $venue->city }}@if($venue->postcode), {{ $venue->postcode }}@endif, {{ $venue->state }}
                    @else
                        –
                    @endif
                </div>
            </td>
            <td>
                <div class="meta-label">Receipt Number</div>
                <div class="receipt-no">{{ $group->group_no }}</div>

                <div class="meta-label" style="margin-top:8px;">Order Placed</div>
                <div class="meta-value small">{{ $group->created_at->timezone('Asia/Kuala_Lumpur')->format('d M Y') }}</div>

                <div class="meta-label" style="margin-top:8px;">Payment Status</div>
                <div class="meta-value small">{{ ucfirst($group->payment_status) }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Order Details ────────────────────────────────────────────────────────── --}}
    <div class="section-body" style="padding-top:14px;">
        <div class="section-title">Order Details</div>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variation</th>
                    <th class="right">Net Product Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group->courtBookings as $item)
                @php
                    $slotDuration = (int) ($item->court?->venueSport?->slot_duration ?? 60);
                    $durationMins = \Carbon\Carbon::parse($item->start_time)->diffInMinutes(\Carbon\Carbon::parse($item->end_time));
                    $slots        = $slotDuration > 0 ? (int) floor($durationMins / $slotDuration) : 1;
                @endphp
                <tr>
                    <td>
                        {{ $item->court?->venueSport?->venue?->name ?? '–' }}<br>
                        <span style="color:#777;font-size:10px;">
                            {{ $item->booking_date->format('d M Y') }} &nbsp;|&nbsp;
                            {{ $item->start_time }} – {{ $item->end_time }}
                        </span>
                    </td>
                    <td>{{ $item->court?->name ?? '–' }}</td>
                    <td class="right">RM {{ number_format($item->price, 2) }}</td>
                    <td class="right">{{ $slots }}</td>
                    <td class="right">RM {{ number_format($item->price * $slots, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Pricing Summary ──────────────────────────────────────────────────────── --}}
    <div class="summary-wrap">
        <table class="summary-table">
            <tr>
                <td class="sl">Merchandise Subtotal</td>
                <td class="sr">RM {{ number_format($group->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="sl">
                    Discount
                    @if($group->voucher)
                        ({{ $group->voucher->code }})
                    @endif
                </td>
                <td class="sr @if($group->discount_amount > 0) discount-val @endif">
                    @if($group->discount_amount > 0)
                        – RM {{ number_format($group->discount_amount, 2) }}
                    @else
                        RM 0.00
                    @endif
                </td>
            </tr>
            <tr>
                <td class="sl">SST (0%)</td>
                <td class="sr">RM 0.00</td>
            </tr>
            <tr>
                <td class="sl">Shipping Fee</td>
                <td class="sr">RM 0.00</td>
            </tr>
            <tr class="total-row">
                <td class="sl">Total Paid</td>
                <td class="sr">RM {{ number_format($group->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────────────────── --}}
    <div class="receipt-footer">
        Generated on {{ now()->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A') }} &nbsp;|&nbsp; XPark Sports &nbsp;|&nbsp; This is a computer-generated receipt.
    </div>

</div>

</body>
</html>
