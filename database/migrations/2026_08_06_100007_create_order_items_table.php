<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            // Snapshots so historical invoices/orders stay correct even if
            // the product/variant is later renamed, repriced, or deleted.
            $table->string('product_name_snapshot');
            $table->string('size_label_snapshot');
            $table->string('sku_snapshot');
            $table->string('hsn_code_snapshot')->nullable();

            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price')->comment('paise');
            $table->unsignedInteger('line_total')->comment('paise');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
