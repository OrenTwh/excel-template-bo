<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('visit.print_qr') }} - {{ $visitData['reference'] ?? '' }}</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                margin: 10mm;
            }
            .page-break {
                page-break-after: always;
            }
            .ticket-card {
                page-break-inside: avoid;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-section h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            color: #333;
        }

        .header-section .reference {
            font-size: 22px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
        }

        .header-section .visit-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #666;
        }

        .header-section .visit-info p {
            margin: 5px 0;
        }

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .ticket-card {
            background: white;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .ticket-card .ticket-header {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .ticket-card .ticket-number {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .ticket-card .ticket-type {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .ticket-card .ticket-price {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .ticket-card .qr-wrapper {
            margin: 15px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .ticket-card .qr-code {
            display: inline-block;
        }

        .ticket-card .ticket-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #999;
        }

        .ticket-card.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .ticket-card.error .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
        }

        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 40px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            z-index: 1000;
        }

        .btn-print:hover {
            background: #0056b3;
            box-shadow: 0 6px 16px rgba(0,123,255,0.4);
        }

        .summary-info {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .summary-info h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }

        .summary-info .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .summary-info .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .summary-info .info-item strong {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-info .info-item span {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="header-section no-print">
        <h1>{{ __('visit.print_qr') }}</h1>
        <div class="reference">{{ $visitData['reference'] ?? '-' }}</div>
        <div class="visit-info">
            <p><strong>{{ __('visit.visit_date') }}:</strong> {{ $visitData['visit_date'] ?? '-' }}</p>
            @if(!empty($visitData['user_name']))
            <p><strong>{{ __('visit.user') }}:</strong> {{ $visitData['user_name'] }}</p>
            @endif
            <p><strong>Total Tickets:</strong> {{ $visitData['total_tickets'] ?? 0 }}</p>
        </div>
    </div>

    <div class="summary-info no-print">
        <h3>Visit Summary</h3>
        <div class="info-grid">
            <div class="info-item">
                <strong>Reference</strong>
                <span>{{ $visitData['reference'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <strong>Visit Date</strong>
                <span>{{ $visitData['visit_date'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <strong>Total Tickets</strong>
                <span>{{ $visitData['total_tickets'] ?? 0 }}</span>
            </div>
            <div class="info-item">
                <strong>Total Amount</strong>
                <span>RM {{ number_format($visitData['total_amount'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="tickets-grid">
        @if(!empty($qrCodes))
            @foreach($qrCodes as $index => $qr)
                <div class="ticket-card {{ isset($qr['error']) ? 'error' : '' }}">
                    <div class="ticket-header">
                        <div class="ticket-number">Ticket #{{ $qr['ticket_number'] }}</div>
                        <div class="ticket-type">{{ $qr['ticket_type_name'] }}</div>
                        <div class="ticket-price">RM {{ number_format($qr['ticket_type_price'] ?? 0, 2) }}</div>
                    </div>

                    <div class="qr-wrapper">
                        @if(isset($qr['error']))
                            <div class="error-message">
                                <strong>QR Generation Failed</strong><br>
                                {{ $qr['message'] ?? 'Unknown error' }}
                            </div>
                        @else
                            <div class="qr-code" id="qr_{{ $qr['ticket_number'] }}"></div>
                        @endif
                    </div>

                    <div class="ticket-footer">
                        <div>{{ $visitData['reference'] ?? '' }}</div>
                        <div>{{ $visitData['visit_date'] ?? '' }}</div>
                        @if(isset($qr['order_id']) && isset($qr['guest_type']))
                        <div style="margin-top: 5px; font-size: 10px;">
                            Order: {{ $qr['order_id'] }} | {{ $qr['guest_type'] }} | Seq: {{ $qr['sequence'] }}
                        </div>
                        @endif
                    </div>
                </div>

                @if(($index + 1) % 6 == 0 && ($index + 1) < count($qrCodes))
                    <div class="page-break"></div>
                @endif
            @endforeach
        @else
            <div class="ticket-card">
                <p>No tickets found</p>
            </div>
        @endif
    </div>

    <button class="btn-print no-print" onclick="window.print()">
        <span>&#128438;</span> {{ __('template.print') }}
    </button>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(!empty($qrCodes))
                @foreach($qrCodes as $qr)
                    @if(!isset($qr['error']))
                        (function() {
                            const qrData = @json($qr['qr_data']);
                            const elementId = 'qr_{{ $qr['ticket_number'] }}';
                            const element = document.getElementById(elementId);

                            if (!element) return;

                            // Use qr_string from ticket data (QRData format)
                            const qrString = '{{ $qr['qr_string'] ?? '' }}';

                            // Check if Turnstile returned a QR code image or data string
                            if (qrData && qrData.qr_image) {
                                // If Turnstile returns base64 image
                                element.innerHTML = '<img src="' + qrData.qr_image + '" alt="QR Code" style="width: 180px; height: 180px;">';
                            } else if (qrData && qrData.qr_string) {
                                // If Turnstile returns QR string to generate
                                new QRCode(element, {
                                    text: qrData.qr_string,
                                    width: 180,
                                    height: 180,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            } else if (qrString) {
                                // Use pre-built QRData string (YYYYMMDDHHMMSS + OrderId + GuestType + Seq + MOBILEAPP)
                                new QRCode(element, {
                                    text: qrString,
                                    width: 180,
                                    height: 180,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            } else {
                                // Final fallback
                                element.innerHTML = '<div style="color: red;">QR Code unavailable</div>';
                            }
                        })();
                    @endif
                @endforeach
            @endif
        });
    </script>
</body>
</html>
