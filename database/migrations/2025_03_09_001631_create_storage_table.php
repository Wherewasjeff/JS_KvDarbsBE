<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageTable extends Migration
{
    public function up()
    {
        Schema::create('storage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // Foreign key to the stores table
            $table->string('image')->default('http://localhost:3000/noimage.png'); // Default image URL
            $table->string('product_name'); // Name of the product
            $table->string('barcode')->unique(); // Barcode, should be unique
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null'); // Optional category
            $table->decimal('price', 10, 2)->nullable(); // Price of the product
            $table->string('shelf_num')->nullable(); // Shelf number
            $table->string('storage_num')->nullable(); // Storage number
            $table->integer('quantity_in_storage')->default(0); // Quantity in storage
            $table->integer('quantity_in_salesfloor')->default(0); // Quantity on sales floor
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('storage');
    }
}
