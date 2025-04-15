<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStorageTableNullableColumns extends Migration
{
    public function up()
    {
        Schema::table('storage', function (Blueprint $table) {
            // Make 'category_id', 'price', 'shelf_num', 'storage_num', 'quantity_in_storage', 'quantity_in_salesfloor', and 'image' nullable
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->decimal('price', 8, 2)->nullable()->change();
            $table->string('shelf_num')->nullable()->change();
            $table->string('storage_num')->nullable()->change();
            $table->integer('quantity_in_storage')->nullable()->change();
            $table->integer('quantity_in_salesfloor')->nullable()->change();
            $table->string('image')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('storage', function (Blueprint $table) {
            // Revert columns back to NOT NULL if you ever need to undo this change
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->decimal('price', 8, 2)->nullable(false)->change();
            $table->string('shelf_num')->nullable(false)->change();
            $table->string('storage_num')->nullable(false)->change();
            $table->integer('quantity_in_storage')->nullable(false)->change();
            $table->integer('quantity_in_salesfloor')->nullable(false)->change();
            $table->string('image')->nullable(false)->change();
        });
    }
}
