<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'NC-'.now()->format('Ym').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'contact_name' => fake()->name(),
            'contact_mobile' => fake()->numerify('9#########'),
            'contact_email' => fake()->safeEmail(),
            'shipping_address' => [
                'line1' => fake()->streetAddress(),
                'line2' => null,
                'city' => fake()->city(),
                'pincode' => '600001',
                'state' => 'Tamil Nadu',
            ],
            'shipping_state' => 'Tamil Nadu',
            'shipping_pincode' => '600001',
            'cod_available_at_order' => true,
            'payment_method' => PaymentMethod::Cod,
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::Placed,
            'subtotal' => 20000,
            'discount_total' => 0,
            'shipping_charge' => 4900,
            'tax_cgst' => 1525,
            'tax_sgst' => 1525,
            'tax_igst' => 0,
            'total' => 24950,
            'coupon_id' => null,
            'coupon_code' => null,
            'stock_decremented_at' => now(),
        ];
    }

    public function razorpayPending(): self
    {
        return $this->state(fn () => [
            'payment_method' => PaymentMethod::Razorpay,
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::PendingPayment,
            'stock_decremented_at' => null,
            'razorpay_order_id' => 'order_'.fake()->unique()->bothify('??????????'),
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn () => ['payment_status' => PaymentStatus::Paid]);
    }
}
