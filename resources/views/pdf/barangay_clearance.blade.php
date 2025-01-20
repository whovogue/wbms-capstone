<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html,
        body {
            margin: 0;
            /* Remove default margins */
            padding: 0;
            /* Remove default padding */
            width: 100%;
            height: 100%;
            /* Ensure full height */
            overflow: hidden;
            /* Prevent scrollbars */
        }

        .full-page {
            width: 100%;
            height: 100%;
            background: url('{{ public_path('images/clearance.png') }}') no-repeat center top;
            /* Position the image at the top */
            background-size: cover;
            /* Cover the entire area */
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>

<body>
    <div class="full-page">
    </div>

    <div style="position: absolute;z-index: 100;margin-top: 228px;margin-left: 308px;">
        <div>
            <span><strong>TO WHOM IT MAY CONCERN:</strong></span>
        </div>

        <div style="margin-top: 15px;">
            <span>This is to certify that according to the records now available in this Barangay the herein
                applicant:</span>
        </div>

        <div style="margin-top: 15px;">
            <span>NAME: <strong>{{ $name }}</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>CIVIL STATUS: <strong>{{ ucfirst($civil_status) }}</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>GENDER: <strong>{{ $gender }}</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>AGE: <strong>{{ $age }} YRS OLD</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>ADDRESS: <strong>{{ $address }}</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>CERT. NO.: <strong>{{ $cert_no }}</strong></span>
        </div>

        <div style="margin-top: 11px;">
            <span>DATE & PLACE OF ISSUANCE: <strong>{{ $date_of_issue }} @
                    BRGY.MAGSAYSAY</strong></span>
        </div>

        <div style="margin-top: 15px;">
            <span>Who has not been charge of any crime nor is there any pending criminal or civil case filed against
                this application before this Barangay on this date.</span>
        </div>

        <div style="margin-top: 15px;">
            <span>This certification is issued upon the request of the applicant in connection with.</span>
        </div>

        <div style="margin-top: 15px;">
            <span>PURPOSE: <strong>{{ $purpose }}</strong></span>
        </div>

        <div style="margin-top: 15px;">
            <span>Given this {{ now()->format('j') }}th day of {{ now()->format('M') }} {{ now()->format('Y') }} at
                Barangay Magsaysay, Carmen, Davao del Norte
        </div>

        <div
            style="margin-top: 40px; margin-left: 120px; padding-top: 10px; border-top: 1px solid black; width: 150px;">
            Signature of Applicant
        </div>

    </div>
</body>

</html>
