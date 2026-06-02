<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_suitability_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('short_label');
            $table->text('detail')->nullable();
            $table->string('severity')->default('info');
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_public', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_suitability_items');
    }
};
