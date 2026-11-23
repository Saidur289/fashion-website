<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general')->index();
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('districts')->nullable(); // null/empty = default fallback zone
            $table->unsignedInteger('charge');
            $table->string('estimated_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index(); // created | updated | deleted | restored | status_changed | payment_verified | stock_adjusted | settings_updated
            $table->string('subject_type')->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'ingredients')) {
                $table->text('ingredients')->nullable()->after('how_to_use');
            }
            if (! Schema::hasColumn('products', 'volume')) {
                $table->string('volume')->nullable()->after('skin_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = array_filter(['ingredients', 'volume'], fn ($c) => Schema::hasColumn('products', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('settings');
    }
};
