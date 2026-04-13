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
        Schema::create('provider_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('event')->nullable()->index();
            $table->string('external_id')->nullable()->index();
            $table->string('signature')->nullable();
            $table->boolean('is_valid_signature')->default(false);
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->string('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_callback_logs');
    }
};
