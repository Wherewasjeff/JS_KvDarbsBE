<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Worker extends Authenticatable {
    use HasFactory, HasApiTokens; // Add HasApiTokens

    protected $fillable = [
        'store_id', 'name', 'lastname', 'age', 'address', 'salary', 'position', 'username', 'password'
    ];

    protected $hidden = ['password'];
}
