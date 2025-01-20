<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function waterConnection(): BelongsTo
    {
        return $this->belongsTo(WaterConnection::class);
    }
}
