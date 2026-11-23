<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->boolean('is_featured')->default(false)->after('is_trending');
            $table->boolean('is_best_seller')->default(false)->after('is_featured');
            // Filled from real orders and reviews later (step 2 and 3); null/0 until then.
            $table->unsignedInteger('sold_count')->default(0)->after('reviews_count');
            $table->decimal('rating', 2, 1)->nullable()->after('sold_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['is_featured', 'is_best_seller', 'sold_count', 'rating']);
        });
        Schema::dropIfExists('brands');
    }
};
