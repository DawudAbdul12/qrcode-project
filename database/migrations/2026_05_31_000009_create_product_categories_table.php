<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_category_id')->nullable()->after('category')->constrained()->nullOnDelete();
        });

        DB::table('products')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select(['company_id', 'category'])
            ->distinct()
            ->get()
            ->each(function (object $product): void {
                $categoryId = DB::table('product_categories')->insertGetId([
                    'company_id' => $product->company_id,
                    'name' => $product->category,
                    'slug' => Str::slug($product->category) ?: 'category',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('products')
                    ->where('company_id', $product->company_id)
                    ->where('category', $product->category)
                    ->update(['product_category_id' => $categoryId]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
        });

        Schema::dropIfExists('product_categories');
    }
};
