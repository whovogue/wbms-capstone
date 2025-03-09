<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            font-size: 14px;
            margin: 15px;
        }

        .bill-table {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .bill-table,
        .bill-table th,
        .bill-table td {
            border: 1px solid #000;
        }

        .bill-table th,
        .bill-table td {
            padding: 5px;
            text-align: left;
        }

        .bill-table .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .bill-table .label {
            font-weight: bold;
        }

        .bill-table .right-align {
            text-align: right;
        }

        .bill-table .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .footer-note {
            font-size: 12px;
            margin-top: 30px;
            max-width: 700px;
            margin: 0 auto;
            text-align: left;
        }


        /* Style for the logo container */
        .logo {
            margin-top: -35px;
            margin-bottom: 15px;
        }

        .logo img {
            width: 100px;
            /* Adjust the width as needed */
            height: auto;
        }

        .headText {
            margin-top: -75px;
            margin-left: 200px;
            position: absolute;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(asset('images/logo.png'))) }}" alt="Logo">
    </div>

    <div class="headText">
        <span><strong>BARANGAY MAGSAYSAY WATER WORKS</strong></span>
        <br>
        <span>Magsaysay, Carmen, Davao del Norte</span>
    </div>

    <div style="font-size: 12px;margin-top: 25px;">
        <span><strong>Account Number : {{ $account_number }}</strong></span><br>
        <span><strong>Bill Date : {{ $bill_date }}</strong></span><br>
    </div>

    <div style="margin-top: 20px;">
        <table class="bill-table">
            <tr>
                <td class="label">Consumer Number:</td>
                <td class="label" colspan="2" style="text-align: right;">
                    {{ auth()->user()->isAdmin() ? '' : $consumer_number }}</td>
                {{-- <td class="label" colspan="2">Previous Balance:</td> --}}
                <td class="label" colspan="2">Remaining Balance:</td>
                <td></td>
                <td
                    style="text-align: right;font-weight: bold; font-family: 'DejaVu Sans', sans-serif; font-size: 13px;">
                    {{-- ₱ {{ $previous_balance }}.00</td> --}}
                    ₱ {{ $previous_bal_display }}.00</td>
            </tr>

            <tr>
                <td colspan="3"></td>
                <td class="label">CURRENT BILL:</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td class="label">Name:</td>
                <td colspan="2" style="text-align: right;">{{ $consumer_name }}</td>
                <td>Minimum Charge in cm³</td>
                <td width="20px" style="text-align: right;">{{ $minimumConsumption }}</td>
                <td style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">₱
                    {{ $minimumValue }}</td>
                <td></td>
            </tr>

            <tr>
                <td class="label">Address:</td>
                <td colspan="2" style="text-align: right;">{{ $purok }}</td>
                <td>Excess in minimum</td>
                <td width="20px" style="text-align: right;">{{ $excess_key }}</td>
                <td style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">
                    ₱ {{ $excess_charge }}.00</td>
                <td style="text-align: right;font-weight: bold;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">
                    ₱ {{ $total_charge - 40 }}.00</td>
            </tr>
            <tr>
                <th class="total" colspan="7">-</th>
            </tr>
            <tr>
                <td class="label" style="border: 0;">Period</td>
                <td width="20px" class="label">From</td>
                <td style="text-align: right;">{{ $period_from }}</td>
                <td class="label">Current Consumption</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="label">Covered</td>
                <td width="20px" class="label">To</td>
                <td style="text-align: right;">{{ $period_to }}</td>
                <td>Distribution Charge</td>
                <td width="20px" style="text-align: right;">20</td>
                <td style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">₱ 20.00</td>
                <td></td>
            </tr>


            <tr>
                <td class="label" colspan="2">No. of Days</td>
                <td>{{ $num_of_days }} Days</td>
                <td>Maintenance Charge</td>
                <td width="20px" style="text-align: right;">20</td>
                <td style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">₱ 20.00</td>
                <td></td>
            </tr>
            <tr>
                <td class="label" colspan="2">Ave cm³/day</td> {{-- Ave cm3/day --}}
                <td>{{ $ave_cm }} cm³</td>
                <td>5% Discount</td>
                <td></td>
                <td style="text-align: center;">{{ $is_discounted }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td class="total" style="font-weight: bold">Sub-total Current Bill</td>
                <td></td>
                <td class="amount" style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px; ">
                    ₱ {{ $total_charge }} </td>
                <td class="label" style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">
                    ₱ {{ $total_charge }}</td>
            </tr>

            <tr>
                <td class="label">Received By:</td>
                <td colspan="2" style="text-align: right;">Book-Keeper</td>
                <td colspan="3" class="label" style="text-align: right;">Paid Amount:</td>
                <td class="label" style="text-align: right;font-family: 'DejaVu Sans', sans-serif;font-size: 13px;">
                    ₱ {{ $paid_amount }}</td>
            </tr>
        </table>
    </div>

    <div class="footer-note" style="margin-top:20px ">
        <p><strong>Note: Should you find any error in your Water Bill, please inform us immediately. If none,
                we shall appreciate very much your early payment. Thank you.</strong></p>
        <p><em>*Discount Cut-off Date is on or before: <strong>{{ $cut_off_date }}</strong></em></p>
        <p><em>*Current bills paid on or before due date is subject for 5% discount</em></p>
        <p><em>
                <strong>*Bills unpaid after next reading will be accumulated and added to the next billing and will not
                    be eligible for 5% Discount.
                </strong>
            </em></p>
    </div>

    <div style="margin-top: 70px; postition:absolute">
        <img style=" background-color: #e5e5e5; padding: 4px;border-radius: 5px;" width="326" height="170"
            src={{ $monthly_consumption }} />

        <img style=" background-color: #e5e5e5; padding: 4px;border-radius: 5px;" width="326" height="170"
            src={{ $monthly_spending }} />
    </div>

    <div style="margin-top: -30px; font-size: 12px">
        <p style="font-size: 14px"><strong>Water Rates and Charges</strong></p>
        <p style="margin-top: 10px"><strong>Minimum Charge = 125.00</strong></p>
        <p><strong>Excess Charge = {{ $excess_charge }}.00</strong></p>
        <p><strong>Distribution Charge = 20.00</strong></p>
        <p><strong>Maintenance Charge = 20.00</strong></p>
    </div>

    <span style="margin-top:-262; position:absolute; font-size:10px;">
        Monthly Consumption
    </span>

    <span style="margin-top:-262; position:absolute; font-size:10px;margin-left:256;">
        Monthly Spending
    </span>

</body>

</html>
<!DOCTYPE html>
