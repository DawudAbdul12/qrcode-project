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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('logo_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('verification_headline')->default('Verify your original product');
            $table->text('genuine_message')->nullable();
            $table->text('suspicious_message')->nullable();
            $table->text('invalid_message')->nullable();
            $table->text('support_text')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('website_url')->nullable();
            $table->json('social_links')->nullable();
            $table->json('faq_items')->nullable();
            $table->json('custom_sections')->nullable();
            $table->text('company_description')->nullable();
            $table->string('manufacturer_name')->nullable();
            $table->text('factory_address')->nullable();
            $table->string('certification_number')->nullable();
            $table->string('layout_style')->default('default');
            $table->string('primary_color')->default('#2563EB');
            $table->string('secondary_color')->default('#111827');
            $table->string('text_color')->default('#111827');
            $table->string('background_color')->default('#FFFFFF');
            $table->string('button_color')->default('#2563EB');
            $table->string('button_text_color')->default('#FFFFFF');
            $table->string('font_family')->default('DM Sans');
            $table->string('badge_style')->default('official');
            $table->string('success_color')->default('#22C55E');
            $table->string('warning_color')->default('#F59E0B');
            $table->string('error_color')->default('#EF4444');
            $table->boolean('show_platform_branding')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
