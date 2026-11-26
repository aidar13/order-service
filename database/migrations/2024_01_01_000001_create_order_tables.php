<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->unsignedBigInteger('price_cents');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            // Identity comes from the gateway; this service has no users table of its own.
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('new')->index();
            $table->string('delivery_address');
            $table->unsignedBigInteger('total_cents');
            $table->string('currency', 3);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Snapshot of the catalogue at purchase time: later price changes must not rewrite history.
            $table->string('sku');
            $table->string('name');
            $table->unsignedBigInteger('unit_price_cents');
            $table->unsignedInteger('quantity');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('amount_cents');
            $table->string('external_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'idempotency_key']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
