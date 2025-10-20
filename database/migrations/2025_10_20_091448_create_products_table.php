<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->string('subcategory');
            $table->decimal('quantity', 8, 2);
            $table->string('unit');
            $table->enum('condition', ['fresh', 'good', 'average', 'expiring_soon']);
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_free')->default(false);
            $table->string('image')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
