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
        Schema::create('ppob_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('brand')->nullable()->index();
            $table->string('buyer_sku_code')->nullable()->index();
            $table->string('markup_type')->default('fixed');
            $table->decimal('markup_value', 15, 2)->default(0);
            $table->decimal('min_markup', 15, 2)->nullable();
            $table->decimal('max_markup', 15, 2)->nullable();
            $table->unsignedInteger('rounding_unit')->default(1);
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppob_pricing_rules');
    }
};
