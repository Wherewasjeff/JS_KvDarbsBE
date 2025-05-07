<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_date',
        'store_id',
    ];

    // Relationship: one shipment has many items
    public function items()
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }
}
