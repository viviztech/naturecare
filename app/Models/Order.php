<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'contact_name',
        'contact_mobile',
        'contact_email',
        'shipping_address',
        'shipping_state',
        'shipping_pincode',
        'cod_available_at_order',
        'payment_method',
        'payment_status',
        'order_status',
        'subtotal',
        'discount_total',
        'shipping_charge',
        'tax_cgst',
        'tax_sgst',
        'tax_igst',
        'total',
        'coupon_id',
        'coupon_code',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'gst_invoice_number',
        'stock_decremented_at',
        'tracking_number',
        'tracking_url',
        'cancelled_at',
        'cancellation_reason',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'cod_available_at_order' => 'boolean',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'order_status' => OrderStatus::class,
            'subtotal' => MoneyCast::class,
            'discount_total' => MoneyCast::class,
            'shipping_charge' => MoneyCast::class,
            'tax_cgst' => MoneyCast::class,
            'tax_sgst' => MoneyCast::class,
            'tax_igst' => MoneyCast::class,
            'total' => MoneyCast::class,
            'stock_decremented_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function scopeOfStatus($query, ?string $status)
    {
        return $query->when($status, fn ($q) => $q->where('order_status', $status));
    }

    public function totalTax(): \App\Support\Money
    {
        return $this->tax_cgst->add($this->tax_sgst)->add($this->tax_igst);
    }

    public function isInterState(): bool
    {
        return $this->tax_igst->paise() > 0;
    }

    public function isCancellable(): bool
    {
        return $this->order_status->isCancellable();
    }

    public function stockWasDecremented(): bool
    {
        return $this->stock_decremented_at !== null;
    }
}
