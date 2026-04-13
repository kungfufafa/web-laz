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
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('fulfillment_message');
            $table->string('midtrans_transaction_id')->nullable()->unique()->after('midtrans_order_id');
            $table->string('midtrans_snap_token')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_redirect_url')->nullable()->after('midtrans_snap_token');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_redirect_url');
            $table->timestamp('midtrans_expired_at')->nullable()->after('midtrans_payment_type');
            $table->json('midtrans_payload')->nullable()->after('midtrans_expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'midtrans_payment_type',
                'midtrans_expired_at',
                'midtrans_payload',
            ]);
        });
    }
};
