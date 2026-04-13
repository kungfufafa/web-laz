<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false);
        });

        $midtransPaymentMethodId = DB::table('payment_methods')
            ->whereRaw('LOWER(name) LIKE ?', ['%midtrans%'])
            ->orderBy('id')
            ->value('id');

        if ($midtransPaymentMethodId !== null) {
            DB::table('payment_methods')->update([
                'is_primary' => false,
            ]);

            DB::table('payment_methods')
                ->where('id', $midtransPaymentMethodId)
                ->update([
                    'is_primary' => true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
