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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., bank_transfer, e_wallet, qris
            $table->string('account_number');
            $table->string('account_holder');
            $table->string('logo')->nullable();
            $table->text('qris_static_payload')->nullable();
            $table->string('qris_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_token')->nullable()->index();
            $table->string('donor_name')->nullable();
            $table->string('donor_phone')->nullable();
            $table->string('donor_email')->nullable();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('category')->default('infak');
            $table->string('payment_type')->nullable();
            $table->string('context_slug')->nullable()->index();
            $table->string('context_label')->nullable();
            $table->string('intention_note')->nullable();
            $table->string('calculator_type')->nullable();
            $table->json('calculator_breakdown')->nullable();
            $table->string('proof_image')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, rejected
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['guest_token', 'created_at']);
        });

        Schema::create('member_prayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedInteger('likes_count')->default(0);
            $table->string('status')->default('published'); // published, hidden
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prayer_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_prayer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'member_prayer_id']);
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('youtube_id');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('prayer_supports');
        Schema::dropIfExists('member_prayers');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('payment_methods');
    }
};
