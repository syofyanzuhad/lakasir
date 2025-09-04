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
        Schema::create('product_additional_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('additional_barcode')->index();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            
            // Ensure unique additional barcode per product
            $table->unique(['product_id', 'additional_barcode']);
            // Index for faster search on non-deleted barcodes
            $table->index(['additional_barcode', 'is_deleted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_additional_barcodes');
    }
};