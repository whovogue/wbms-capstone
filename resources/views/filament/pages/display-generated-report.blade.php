<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .header,
        .footer {
            text-align: center;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .content {
            margin: 20px 0;
        }

        .date-range {
            font-size: 16px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f5f5f5;
        }

        .date1 {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Payment Report</h1>
    </div>

    <div class="content">
        <div class="date-range">
            From: {{ \Carbon\Carbon::parse($from)->format('F j, Y') }}
            To: {{ \Carbon\Carbon::parse($to)->format('F j, Y') }}
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Reference ID</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Paid Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->waterConnection->reference_id }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">₱{{ number_format($payment->amount, 2) }}
                        </td>
                        <td>{{ $payment->created_at->format('F j, Y') }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">₱{{ ucfirst($payment->partial_payment) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No payments found for the selected date range.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                    <td style="font-family: 'DejaVu Sans', sans-serif;" colspan="3">₱{{ number_format($total, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="date1" style="margin-top: 25px;">
            <div class="signature-label">Prepared by : {{ auth()->user()->name }}</div>
        </div>

    </div>

    <div class="footer">
        <p>&copy; {{ now()->year }} Magsaysay Water Works. All rights reserved.</p>
    </div>

</body>

</html>
