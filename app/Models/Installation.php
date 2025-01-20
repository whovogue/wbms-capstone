<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function waterConnection(): BelongsTo
    {
        return $this->belongsTo(WaterConnection::class);
    }
}
