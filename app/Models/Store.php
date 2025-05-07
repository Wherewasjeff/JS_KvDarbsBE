<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'storename',
        'address',
        'category',
        'backroom',
    ];
    public function workinghours()
    {
        return $this->hasMany(Workinghours::class);
    }
}
