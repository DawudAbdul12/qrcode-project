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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('production_location')->nullable();
            $table->string('qa_status')->default('pending');
            $table->string('certificate_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
