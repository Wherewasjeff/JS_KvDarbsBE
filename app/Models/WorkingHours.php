<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workinghours extends Model
{
    use HasFactory;
    
    protected $table = 'workinghours'; // Explicit table name
    
    protected $fillable = [
        'store_id',
        'day',
        'opening_time',
        'closing_time',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}