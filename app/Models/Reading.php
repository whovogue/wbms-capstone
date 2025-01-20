<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reading extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function waterConnection(): BelongsTo
    {
        return $this->belongsTo(WaterConnection::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'biller_user_id');
    }
}
