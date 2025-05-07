<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomWorkingHour extends Model
{
    protected $table = 'customworkinghours';
    protected $fillable = [
      'date',
      'opening_time',
      'closing_time',
      'comment',
      'store_id'
    ];
}
