<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameColumn('tripay_reference', 'payment_gateway_reference');
        $this->renameColumn('tripay_merchant_ref', 'payment_gateway_order_id');
        $this->renameColumn('tripay_checkout_url', 'payment_gateway_checkout_url');
        $this->renameColumn('tripay_pay_url', 'payment_gateway_pay_url');
        $this->renameColumn('tripay_pay_code', 'payment_gateway_pay_code');
        $this->renameColumn('tripay_expired_at', 'payment_gateway_expired_at');
        $this->renameColumn('tripay_payload', 'payment_gateway_payload');
    }

    public function down(): void
    {
        $this->renameColumn('payment_gateway_reference', 'tripay_reference');
        $this->renameColumn('payment_gateway_order_id', 'tripay_merchant_ref');
        $this->renameColumn('payment_gateway_checkout_url', 'tripay_checkout_url');
        $this->renameColumn('payment_gateway_pay_url', 'tripay_pay_url');
        $this->renameColumn('payment_gateway_pay_code', 'tripay_pay_code');
        $this->renameColumn('payment_gateway_expired_at', 'tripay_expired_at');
        $this->renameColumn('payment_gateway_payload', 'tripay_payload');
    }

    private function renameColumn(string $from, string $to): void
    {
        if (! Schema::hasColumn('ppob_transactions', $from) || Schema::hasColumn('ppob_transactions', $to)) {
            return;
        }

        Schema::table('ppob_transactions', function (Blueprint $table) use ($from, $to): void {
            $table->renameColumn($from, $to);
        });
    }
};
