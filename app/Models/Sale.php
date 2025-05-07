<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Storage;
use App\Models\User;
use App\Models\Worker;

class Sale extends Model
{
    public $timestamps = false;

    protected $fillable = [
  'store_id','product_id','user_id','worker_id',
  'price','sold','date','time',
  'discount_percent','discount_reason',
  'total_discount_percent','total_discount_reason',
    ];

    public function product()
    {
        return $this->belongsTo(Storage::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    /** 
     * Returns the seller’s name, whether user or worker 
     */
    public function getSellerNameAttribute()
    {
        if ($this->worker) {
            return "{$this->worker->name} {$this->worker->lastname}";
        }
        if ($this->user) {
            return "{$this->user->name} {$this->user->last_name}";
        }
        return '—';
    }
}
