<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {  Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('title')->index();
        $table->string('thumbnail');
        $table->text('description');
        $table->decimal('price')->index();
        $table->decimal('weight');
        $table->tinyInteger('status')->default(1);
        $table->BigInteger('saler_id')->index();
        $table->BigInteger('brand_id')->index();
        $table->BigInteger('category_id')->index();
        $table->timestamps();
        $table->index(['category_id', 'brand_id'],"idx_brand");
            });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::dropIfExists('products', function (Blueprint $table){
        $table->dropIndex(['title']);
        $table->dropIndex(['idx_brand']);
    });
}
};

