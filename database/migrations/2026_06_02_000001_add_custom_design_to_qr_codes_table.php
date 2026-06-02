<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->string('custom_path')->nullable()->unique()->after('verification_url');
            $table->string('foreground_color', 7)->default('#111827')->after('revocation_reason');
            $table->string('background_color', 7)->default('#ffffff')->after('foreground_color');
            $table->string('label_text')->nullable()->after('background_color');
            $table->boolean('show_label')->default(false)->after('label_text');
            $table->boolean('show_logo')->default(false)->after('show_label');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropColumn([
                'custom_path',
                'foreground_color',
                'background_color',
                'label_text',
                'show_label',
                'show_logo',
            ]);
        });
    }
};
