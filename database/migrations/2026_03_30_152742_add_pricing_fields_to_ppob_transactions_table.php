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
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->foreignId('ppob_pricing_rule_id')->nullable()->after('ppob_product_id')->constrained()->nullOnDelete();
            $table->decimal('provider_price', 15, 2)->default(0)->after('inquiry_expires_at');
            $table->decimal('markup_amount', 15, 2)->default(0)->after('provider_price');
        });

        DB::table('ppob_transactions')->update([
            'provider_price' => DB::raw('base_price'),
            'markup_amount' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppob_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppob_pricing_rule_id');
            $table->dropColumn([
                'provider_price',
                'markup_amount',
            ]);
        });
    }
};
