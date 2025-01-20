<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Mail\TwoFactorMail;
use Exception;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory,
        Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isConsumer(): bool
    {
        return $this->role === 'consumers';
    }

    public function isMeterReader(): bool
    {
        return $this->role === 'reader';
    }

    public function isClerk(): bool
    {
        return $this->role === 'clerk';
    }

    // public function waterConnection(): BelongsTo
    // {
    //     return $this->belongsTo(WaterConnection::class);
    // }

    public function waterConnections()
    {
        return $this->belongsToMany(WaterConnection::class, 'water_connections_users', 'user_id', 'water_connection_id')
            ->withPivot('status');
    }

    public function disconnectedUsers()
    {
        return $this->hasMany(DisconnectedUser::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return file_exists(public_path('profile-photos/'.$this->profile_photo_path))
            ? asset('profile-photos/'.$this->profile_photo_path)
            : $this->profile_photo_url;
    }

    // asset('storage/'.$this->profile_photo_path)

    public function generateCode()
    {
        $code = rand(100000, 999999);

        UserCodes::updateOrCreate(
            ['user_id' => auth()->user()->id],
            ['code' => $code]
        );

        try {

            $details = [
                'title' => "Email from Public Attorney's Office",
                'code' => $code,
                'name' => auth()->user()->name,
            ];

            Mail::to(auth()->user()->email)->send(new TwoFactorMail($details));
        } catch (Exception $e) {
            info('Error: '.$e->getMessage());
        }
    }

    public function userCodes()
    {
        return $this->hasMany(UserCodes::class);
    }
}
