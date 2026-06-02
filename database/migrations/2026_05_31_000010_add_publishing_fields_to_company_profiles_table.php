<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('certification_number');
            $table->string('button_style')->default('solid')->after('button_text_color');
            $table->string('publication_status')->default('draft')->after('show_platform_branding');
            $table->timestamp('published_at')->nullable()->after('publication_status');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['certificate_path', 'button_style', 'publication_status', 'published_at']);
        });
    }
};
