<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id'); // Foreign key for store
            $table->string('category');
            $table->timestamps();

            // Define foreign key constraint
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};


