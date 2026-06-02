<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('activated_at');
            $table->string('revocation_reason')->nullable()->after('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropColumn(['expires_at', 'revocation_reason']);
        });
    }
};
