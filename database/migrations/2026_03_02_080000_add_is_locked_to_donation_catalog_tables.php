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
        if (Schema::hasTable('donation_categories') && ! Schema::hasColumn('donation_categories', 'is_locked')) {
            Schema::table('donation_categories', function (Blueprint $table): void {
                $table->boolean('is_locked')->default(false)->after('is_active');
                $table->index(['is_locked']);
            });
        }

        if (Schema::hasTable('donation_payment_types') && ! Schema::hasColumn('donation_payment_types', 'is_locked')) {
            Schema::table('donation_payment_types', function (Blueprint $table): void {
                $table->boolean('is_locked')->default(false)->after('is_active');
                $table->index(['is_locked']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('donation_categories') && Schema::hasColumn('donation_categories', 'is_locked')) {
            Schema::table('donation_categories', function (Blueprint $table): void {
                $table->dropIndex(['is_locked']);
                $table->dropColumn('is_locked');
            });
        }

        if (Schema::hasTable('donation_payment_types') && Schema::hasColumn('donation_payment_types', 'is_locked')) {
            Schema::table('donation_payment_types', function (Blueprint $table): void {
                $table->dropIndex(['is_locked']);
                $table->dropColumn('is_locked');
            });
        }
    }
};
