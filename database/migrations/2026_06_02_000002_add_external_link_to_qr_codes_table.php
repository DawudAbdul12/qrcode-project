<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->string('external_link_label')->nullable()->after('show_logo');
            $table->string('external_link_url', 2048)->nullable()->after('external_link_label');
            $table->boolean('show_external_link')->default(false)->after('external_link_url');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropColumn([
                'external_link_label',
                'external_link_url',
                'show_external_link',
            ]);
        });
    }
};
