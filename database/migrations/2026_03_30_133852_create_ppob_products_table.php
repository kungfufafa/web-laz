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
    {
        Schema::create('ppob_products', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('digiflazz');
            $table->string('service_type');
            $table->string('category')->nullable()->index();
            $table->string('brand')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('product_name');
            $table->string('seller_name')->nullable();
            $table->string('buyer_sku_code');
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedInteger('admin')->nullable();
            $table->unsignedInteger('commission')->nullable();
            $table->boolean('buyer_product_status')->default(true);
            $table->boolean('seller_product_status')->default(true);
            $table->boolean('unlimited_stock')->nullable();
            $table->unsignedBigInteger('stock')->nullable();
            $table->boolean('multi')->nullable();
            $table->string('start_cut_off')->nullable();
            $table->string('end_cut_off')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'buyer_sku_code']);
            $table->index(['service_type', 'buyer_product_status', 'seller_product_status'], 'ppob_products_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppob_products');
    }
};
