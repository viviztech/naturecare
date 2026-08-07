<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            // No FK constraint to coupons here on purpose: the coupons table
            // is created later in the migration order (§1 build order is
            // fixed). coupon_code is a display snapshot; coupon_id is
            // resolved/validated in PHP by CartService at apply-time.
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code')->nullable();
            $table->unsignedInteger('subtotal')->default(0)->comment('paise');
            $table->unsignedInteger('discount_total')->default(0)->comment('paise');
            $table->unsignedInteger('total')->default(0)->comment('paise');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
