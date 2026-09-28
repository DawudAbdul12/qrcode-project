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
        Schema::table('scan_logs', function (Blueprint $table) {
            $table->foreignId('label_code_id')->nullable()->after('qr_code_id')->constrained()->nullOnDelete();
            $table->index(['label_code_id', 'status_result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_logs', function (Blueprint $table) {
            $table->dropIndex(['label_code_id', 'status_result']);
            $table->dropConstrainedForeignId('label_code_id');
        });
    }
};
