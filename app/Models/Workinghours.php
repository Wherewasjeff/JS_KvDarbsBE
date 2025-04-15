<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workinghours extends Model
{
    /** @use HasFactory<\Database\Factories\WorkinghoursFactory> */
    use HasFactory;
    protected $fillable = [
        'store_id',
        'day',
        'opening_time',
        'closing_time',
    ];
    public function user(): BelongsTo{
        return $this->belongsTo(Store::class);
    }
}
