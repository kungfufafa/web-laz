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
        Schema::create('ppob_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ppob_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('digiflazz');
            $table->string('service_type');
            $table->string('buyer_sku_code');
            $table->string('product_name');
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('type')->nullable();
            $table->string('customer_no');
            $table->string('customer_name')->nullable();
            $table->string('inquiry_reference')->nullable()->index();
            $table->json('inquiry_payload')->nullable();
            $table->timestamp('inquiry_expires_at')->nullable();
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('fee_customer', 15, 2)->default(0);
            $table->decimal('fee_merchant', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->string('payment_channel_code');
            $table->string('payment_channel_name')->nullable();
            $table->string('payment_status')->default('unpaid')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('fulfillment_status')->default('pending')->index();
            $table->text('fulfillment_message')->nullable();
            $table->string('payment_gateway_reference')->nullable()->unique();
            $table->string('payment_gateway_order_id')->unique();
            $table->string('payment_gateway_checkout_url')->nullable();
            $table->string('payment_gateway_pay_url')->nullable();
            $table->string('payment_gateway_pay_code')->nullable();
            $table->timestamp('payment_gateway_expired_at')->nullable();
            $table->json('payment_gateway_payload')->nullable();
            $table->string('digiflazz_ref_id')->unique();
            $table->string('digiflazz_status')->nullable();
            $table->string('digiflazz_rc')->nullable();
            $table->string('digiflazz_sn')->nullable();
            $table->json('digiflazz_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppob_transactions');
    }
};
