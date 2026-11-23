<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // stored upper-case
            $table->string('type')->default('percent'); // percent | fixed
            $table->unsignedInteger('value');           // % or Taka
            $table->unsignedInteger('min_order')->default(0);
            $table->unsignedInteger('max_discount')->nullable(); // cap for percent coupons
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // guest checkout allowed

            $table->string('name');
            $table->string('phone', 20)->index();
            $table->string('email')->nullable();
            $table->string('district');
            $table->string('area')->nullable();
            $table->text('address');

            // "Ship to a different address"
            $table->string('ship_name')->nullable();
            $table->string('ship_phone', 20)->nullable();
            $table->string('ship_district')->nullable();
            $table->string('ship_area')->nullable();
            $table->text('ship_address')->nullable();

            $table->text('note')->nullable();

            $table->unsignedInteger('subtotal');
            $table->string('coupon_code')->nullable();
            $table->unsignedInteger('discount')->default(0);
            $table->string('shipping_method'); // inside_dhaka | outside_dhaka | pickup
            $table->unsignedInteger('shipping');
            $table->unsignedInteger('total');

            $table->string('payment_method');                 // cod | bkash
            $table->string('payment_status')->default('unpaid'); // unpaid | pending_check | paid | refunded
            $table->string('bkash_sender', 20)->nullable();
            $table->string('bkash_trx_id')->nullable();

            $table->string('status')->default('pending')->index(); // pending | confirmed | shipped | delivered | cancelled | returned
            $table->string('tracking_code')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            // Copies kept so the order stays correct even if the product changes later.
            $table->string('name');
            $table->string('variant')->nullable();
            $table->string('sku')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('total');
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
    }
};
