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
        Schema::table('ppob_products', function (Blueprint $table) {
            $table->foreignId('ppob_pricing_rule_id')->nullable()->after('buyer_sku_code')->constrained()->nullOnDelete();
            $table->decimal('provider_price', 15, 2)->nullable()->after('ppob_pricing_rule_id');
            $table->unsignedInteger('provider_admin')->nullable()->after('provider_price');
            $table->unsignedInteger('provider_commission')->nullable()->after('provider_admin');
            $table->decimal('sell_price', 15, 2)->nullable()->after('provider_commission');
            $table->decimal('markup_amount', 15, 2)->default(0)->after('sell_price');
        });

        DB::table('ppob_products')->update([
            'provider_price' => DB::raw('price'),
            'provider_admin' => DB::raw('admin'),
            'provider_commission' => DB::raw('commission'),
            'sell_price' => DB::raw('price'),
            'markup_amount' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppob_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppob_pricing_rule_id');
            $table->dropColumn([
                'provider_price',
                'provider_admin',
                'provider_commission',
                'sell_price',
                'markup_amount',
            ]);
        });
    }
};
