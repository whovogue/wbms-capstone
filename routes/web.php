<?php

use App\Http\Controllers\PDFController;
use App\Http\Controllers\SocialiteController;
use App\Livewire\TwoFactor;
use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::redirect('/', '/app');

Route::get('/login', function () {
    return redirect('/app/login');
})->name('login');

Route::get('/view-document/{requestDocument}', [PDFController::class, 'viewDocument'])->name('view-document')->middleware('auth');

Route::get('/generate-bill-pdf/{bill}', [PDFController::class, 'generateBillPDF'])->name('generate-bill-pdf')->middleware('auth');

Route::post('/chart/save', [PDFController::class, 'saveChart'])->name('chart.save')->middleware('auth');

Route::post('/spending-chart/save', [PDFController::class, 'saveSpendingChart'])->name('spending-chart.save')->middleware('auth');

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('socialite.redirect');

Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->name('socialite.callback');

Route::get('2fa', TwoFactor::class)->name('2fa.index');
// ->middleware('redirect2FA')

// FOR TESTING BARANGAY ID PDF

// Route::get('/test-id', function () {
//     $custom_fields = [
//         "name" => "Alyen Apayor",
//         "purok" => "6A-1",
//         "e_sign" => true,
//         "gender" => "Male",
//         "height" => "5'9",
//         "weight" => "75",
//         "blood_type" => "O",
//         "citizenship" => "Filipino",
//         "civil_status" => "single",
//         "date_of_birth" => "2001-11-02",
//         "control_number" => "2025-069",
//         "emergency_name" => "Alyen Apayor",
//         "emergency_address" => "Prk.Sili Gredu Panabo City, Davao Del Norte",
//         "emergency_relation" => "Classmate",
//         "emergency_contact_number" => "09665633043",
//     ];

//     $pdf = PDF::loadView('pdf.test-id', [
//         'name' => $custom_fields['name'],
//         'purok' => $custom_fields['purok'],
//         'e_sign' => $custom_fields['e_sign'],
//         'gender' => $custom_fields['gender'],
//         'height' => $custom_fields['height'],
//         'weight' => $custom_fields['weight'],
//         'blood_type' => $custom_fields['blood_type'],
//         'citizenship' => $custom_fields['citizenship'],
//         'civil_status' => $custom_fields['civil_status'],
//         'date_of_birth' => $custom_fields['date_of_birth'],
//         'control_number' => $custom_fields['control_number'],
//         'emergency_name' => $custom_fields['emergency_name'],
//         'emergency_address' => $custom_fields['emergency_address'],
//         'emergency_relation' => $custom_fields['emergency_relation'],
//         'emergency_contact_number' => $custom_fields['emergency_contact_number'],
//         'age' => \Carbon\Carbon::parse($custom_fields['date_of_birth'])->age,
//     ]);
    
//     return $pdf->stream('test-id.pdf');  // Download the generated PDF
// });


