<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterConnection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'water_connection_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'water_connections_users', 'water_connection_id', 'user_id')
            ->withPivot('status')
            ->whereNot('users.id', auth()->user()->id);
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}
