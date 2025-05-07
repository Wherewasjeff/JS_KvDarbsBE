<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    // protected $table = 'shipment_items'; // rarely needed

    protected $fillable = [
        'shipment_id',
        'product_id',
        'amount',
    ];

    // Inverse relation: an item belongs to its shipment
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    // And to the actual stored product:
    public function product()
    {
        return $this->belongsTo(\App\Models\Storage::class, 'product_id');
    }
}
