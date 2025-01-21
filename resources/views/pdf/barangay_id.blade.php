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
            background: url('{{ public_path('images/barangayid.jpg') }}') no-repeat center top;
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

    <div style="z-index: 100">
        <div
                {{-- EDIT SA margin top from 185px--}}

            style="z-index: 100; margin-top: 210px; margin-left: 236px; position: absolute; transform: translateX(-50%);">
            <span style="border-bottom: 1px solid black; padding-bottom: 2px; font-weight: bold; font-size: 16px;">
                {{ $name }}
            </span>
        </div>
                {{-- edit sa margin top  from 224px --}}
        <div style="z-index: 100; margin-top: 236px; margin-left: 282px;">
            <span style="border-bottom: 1px solid black; padding-bottom: -1px;font-size: 11px;">
                {{ $purok }}
            </span>
        </div>
                {{-- edit sa margin top  from -106.5px --}}
        <div style="z-index: 100; margin-top: -118.5px;margin-left: 503px;">
            <span style="font-size: 12px">
                {{ $date_of_birth }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px + 12 --}}
        <div style="z-index: 100; margin-top: -6px; margin-left: 453px;">
            <span style="font-size: 12px;">
                {{ $age }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="z-index: 100; margin-top: -6px; margin-left: 462px;">
            <span style="font-size: 12px;">
                :{{ ucfirst($civil_status) }}
            </span>
        </div>
                {{-- edit sa margin top  from -5px --}}
        <div style="z-index: 100; margin-top: -5px; margin-left: 492px;">
            <span style="font-size: 12px;">
                {{ ucfirst($citizenship) }}
            </span>

            {{-- <span style="font-size: 12px; margin-left: 99px;">
                {{ ucfirst($height) }} cm
            </span> --}}
        </div>
                {{-- edit sa margin top  from 24px --}}
        <div style="margin-top: 24px; margin-left: 466px">
            <span style="font-size: 12px;">
                {{ $emergency_name }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="margin-top: -6px; margin-left: 504px">
            <span style="font-size: 12px;">
                {{ $emergency_relation }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="margin-top: -6px; margin-left: 480px">
            <span style="font-size: 12px;">
                {{ $emergency_address }}
            </span>
        </div>
                {{-- edit sa margin top  from -5px --}}
        <div style="margin-top: -5px; margin-left: 500px">
            <span style="font-size: 12px;">
                {{ $emergency_contact_number }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="margin-top: -6px; margin-left: 495px">
            <span style="font-size: 12px;">
                December 31, {{ now()->format('Y') }}
            </span>
        </div>
    </div>
                {{-- edit sa margin top  from 10px --}}
    <div style="z-index: 100">
        <span style="font-size: 15px; color: yellow; margin-top: 10px; margin-left: 82px;font-weight: bold;">
            ID NO. {{ $id_no }}
        </span>
    </div>

    <div style="z-index: 1; margin-left: 625px;margin-top: -174px;position: absolute;">
        <span style="font-size: 12px;">
            {{ ucfirst($gender) }}
        </span>
    </div>

    <div style="z-index: 1; margin-left: 665px;margin-top: -161px;position: absolute;">
        <span style="font-size: 12px;">
            {{ ucfirst($blood_type) }}
        </span>
    </div>

    <div style="z-index: 1; margin-left: 640px;margin-top: -147px;position: absolute;">
        <span style="font-size: 12px;">
            {{ ucfirst($weight) }} kg
        </span>
    </div>

    <div style="z-index: 1; margin-left: 640px;margin-top: -134px;position: absolute;">
        <span style="font-size: 12px;">
            {{ ucfirst($height) }} cm
        </span>
    </div>
</body>

</html>
