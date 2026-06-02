<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('ingredients')->nullable()->after('description');
            $table->text('usage_instructions')->nullable()->after('ingredients');
            $table->json('gallery_paths')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ingredients', 'usage_instructions', 'gallery_paths']);
        });
    }
};
