<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Prefix-overlap (e.g. "6" vs "600") is resolved in PHP by
            // longest-prefix-wins in ShippingZoneResolver — not in SQL — so
            // it behaves identically on sqlite (tests) and MySQL (prod).
            $table->string('pincode_prefix');
            $table->unsignedInteger('shipping_charge')->comment('paise');
            $table->boolean('cod_available')->default(true);
            $table->unsignedInteger('free_shipping_above')->nullable()->comment('paise');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('pincode_prefix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
