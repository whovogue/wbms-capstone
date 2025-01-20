<?php

namespace App\Services;

use App\Mail\AnnouncementRequest;
use App\Mail\EmailRequest;
use App\Mail\PaymentRequest;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function handle($users, array $data, $type)
    {

        if ($type == 'reading') {
            $users->each(function ($user) use ($data) {
                Mail::to($user->email)->send(new EmailRequest($user, $data));
            });
        }

        if ($type == 'paymentFull') {

            $users->each(function ($user) use ($data) {
                Mail::to($user->email)->send(new PaymentRequest($user, $data));
            });
        }

        if ($type == 'announcement') {
            $users->each(function ($user) use ($data) {
                Mail::to($user->email)->send(new AnnouncementRequest($user, $data));
            });
        }
    }
}
