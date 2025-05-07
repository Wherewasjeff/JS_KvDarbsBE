<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    use HasFactory;

    protected $table = 'storage'; // Explicitly define the table name

    protected $fillable = [
        'store_id',
        'product_name',
        'sku',
        'barcode',
        'category_id',
        'price',
        'shelf_num',
        'storage_num',
        'quantity_in_storage',
        'quantity_in_salesfloor',
        'should_be',
        'image'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    // In app/Models/Storage.php
    public function sales()
    {
        return $this->hasMany(Sale::class, 'product_id');
    }
}
