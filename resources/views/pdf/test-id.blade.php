{{-- FOR TESTING PURPOSES ONLY --}}
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
        background: url('{{ public_path('images/barangayid.png') }}') no-repeat center top;
        background-size: cover;
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
        style="z-index: 100; margin-top: 120px; margin-left: 236px; position: absolute; transform: translateX(-56%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 12px;">
        Republic of the Philipines
        </span>
        </div>
        <div
        style="z-index: 100; margin-top: 132px; margin-left: 236px; position: absolute; transform: translateX(-56%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 12px;">
        Province of Davao del Norte
        </span>
        </div>
        <div
        style="z-index: 100; margin-top: 145px; margin-left: 236px; position: absolute; transform: translateX(-56%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 12px;">
        Municipality of Carmen
        </span>
        </div>
        <div
        style="z-index: 100; margin-top: 158px; margin-left: 236px; position: absolute; transform: translateX(-56%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 10px;">
        BARANGAY MAGSAYSAY
        </span>
        </div> 
        <div
                {{-- EDIT SA margin top from 185px--}}

            style="z-index: 100; margin-top: 210px; margin-left: 236px; position: absolute; transform: translateX(-50%);">
            <span style="border-bottom: 1px solid black; padding-bottom: 2px; font-weight: bold; font-size: 16px;">
                {{ $name }}
            </span>
        </div>
        <div
        style="z-index: 100; margin-top: 225px; margin-left: 236px; position: absolute; transform: translateX(-56%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 11px;">
        Name
        </span>
        </div> 
        <div
        style="z-index: 100; margin-top: 238px; margin-left: 236px; position: absolute; transform: translateX(-52%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 14px;">
        Is a Bonafide resident of Purok {{ $purok }} Magsaysay
        </span>
        </div>
        <div
        style="z-index: 100; margin-top: 253px; margin-left: 236px; position: absolute; transform: translateX(-52%);">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 14px;">
        Carmen Davao del norte
        </span>
        </div> 


        <div
        style="z-index: 100; margin-top: 281px; margin-left: 268px; position: absolute;">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 11px;">
        SALVADOR S. COCO
        </span>
        </div> 
        <div
        style="z-index: 100; margin-top: 290px; margin-left: 290px; position: absolute;">
        <span style=" padding-bottom: 2px; font-weight: bold; font-size: 9px;">
        Punong Barangay
        </span>
        </div> 


        <div style="z-index: 100; margin-top: 136px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px">
                Date of Birth: {{ $date_of_birth }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px + 12 --}}
        <div style="z-index: 100; margin-top: 149px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Age: {{ $age }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="z-index: 100; margin-top: 162px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Status: {{ ucfirst($civil_status) }}
            </span>
        </div>
                {{-- edit sa margin top  from -5px --}}
        <div style="z-index: 100; margin-top: 176px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Citizenship: {{ ucfirst($citizenship) }}
            </span>

            {{-- <span style="font-size: 12px; margin-left: 99px;">
                {{ ucfirst($height) }} cm
            </span> --}}
        </div>
                {{-- edit sa margin top  from 24px --}}
        <div style="z-index: 100; margin-top: 220px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Name: {{ $emergency_name }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="z-index: 100; margin-top: 233px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Relationship: {{ $emergency_relation }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="z-index: 100; margin-top: 246px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Address: {{ $emergency_address }}
            </span>
        </div>
                {{-- edit sa margin top  from -5px --}}
        <div style="z-index: 100; margin-top: 259px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Contact No: {{ $emergency_contact_number }}
            </span>
        </div>
                {{-- edit sa margin top  from -6px --}}
        <div style="z-index: 100; margin-top: 272px; margin-left: 425px; position: absolute;">
            <span style="font-size: 12px;">
                Valid Until: December 31, {{ now()->format('Y') }}
            </span>
        </div>
    </div>
                {{-- edit sa margin top  from 10px --}}

    <div
    style="z-index: 100; margin-top: 300px; margin-left: 65px; position: absolute;">
    <span style=" padding-bottom: 2px; font-weight: bold; font-size: 15px; color: yellow;">
        ID NO. {{ $control_number }}
    </span>
    </div>
    {{-- <div style="z-index: 100">
        <span style="font-size: 15px; color: yellow; margin-top: 10px; margin-left: 82px;font-weight: bold;">
            ID NO. {{ $control_number }}
        </span>
    </div> --}}

    <div style="z-index: 100; margin-top: 136px; margin-left: 598px; position: absolute;">
        <span style="font-size: 12px;">
            Sex: {{ ucfirst($gender) }}
        </span>
    </div>

    <div style="z-index: 100; margin-top: 149px; margin-left: 598px; position: absolute;">
        <span style="font-size: 12px;">
            Blood Type: {{ ucfirst($blood_type) }}
        </span>
    </div>

    <div style="z-index: 100; margin-top: 162px; margin-left: 598px; position: absolute;">
        <span style="font-size: 12px;">
            Weight: {{ ucfirst($weight) }} kg
        </span>
    </div>

    <div style="z-index: 100; margin-top: 176px; margin-left: 598px; position: absolute;">
        <span style="font-size: 12px;">
            Height: {{ ucfirst($height) }} ft
        </span>
    </div>
    <div style="z-index: 100; margin-top: 200px; margin-left: 425px; position: absolute; color: red; font-weight: bold;">
        <span style="font-size: 11px;">
            PERSON TO BE NOTIFIED IN CASE OF EMERGENCY
        </span>
    </div>

    <div style="z-index: 100; margin-top: 278px; margin-left: 640px; position: absolute;">
        <span style="font-size: 11px; font-weight: bold;">
            _______________
        </span>
    </div>

    <div style="z-index: 100; margin-top: 290px; margin-left: 645px; position: absolute;">
        <span style="font-size: 11px;">
            Bearer Signature
        </span>
    </div>

    <div style="z-index: 100; margin-top: 121px; margin-left: 61px; position: absolute;">
        <img src="{{ public_path('images/logo.png') }}" alt="logo" style="width: 75px; height: 77px;">
    </div>
    
</body>

</html>
