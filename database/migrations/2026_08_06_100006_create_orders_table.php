<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('customer_id')->constrained();

            // Contact + address snapshot at time of order — deliberately not
            // FK-derived at read time, so a later customer edit never
            // silently rewrites history on a placed order.
            $table->string('contact_name');
            $table->string('contact_mobile');
            $table->string('contact_email')->nullable();
            $table->json('shipping_address');

            // Shipping zone snapshot: no FK to shipping_zones (created later
            // in migration order, and the rate must stay fixed even if the
            // admin edits the zone afterwards).
            $table->string('shipping_state')->nullable();
            $table->string('shipping_pincode');
            $table->boolean('cod_available_at_order')->default(false);

            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('order_status');

            $table->unsignedInteger('subtotal')->comment('paise');
            $table->unsignedInteger('discount_total')->default(0)->comment('paise');
            $table->unsignedInteger('shipping_charge')->default(0)->comment('paise');
            $table->unsignedInteger('tax_cgst')->default(0)->comment('paise');
            $table->unsignedInteger('tax_sgst')->default(0)->comment('paise');
            $table->unsignedInteger('tax_igst')->default(0)->comment('paise');
            $table->unsignedInteger('total')->comment('paise');

            // Coupon snapshot — no FK to coupons (created later in migration
            // order); PHP validates coupon_id against the coupons table.
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code')->nullable();

            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();

            $table->string('gst_invoice_number')->nullable()->unique();

            // Additive beyond the literal spec: tells cancellation logic
            // whether stock was ever actually taken. COD decrements on
            // placement; Razorpay only decrements after payment is
            // confirmed — a never-paid Razorpay order must not restore
            // stock it never removed.
            $table->timestamp('stock_decremented_at')->nullable();

            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['order_status']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
