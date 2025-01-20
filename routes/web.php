<?php

use App\Http\Controllers\PDFController;
use App\Http\Controllers\SocialiteController;
use App\Livewire\TwoFactor;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::redirect('/', '/app');

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
