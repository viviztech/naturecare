<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use App\Enums\PartnerType;
use Illuminate\Database\Eloquent\Model;

class BusinessEnquiry extends Model
{
    protected $fillable = [
        'partner_type',
        'name',
        'firm_name',
        'mobile',
        'email',
        'state',
        'district',
        'city',
        'investment_range',
        'years_in_business',
        'current_business',
        'has_godown',
        'message',
        'status',
        'admin_notes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'partner_type' => PartnerType::class,
            'status' => EnquiryStatus::class,
            'has_godown' => 'boolean',
        ];
    }

    protected $attributes = [
        'status' => 'new',
    ];

    public function whatsappUrl(): string
    {
        return 'https://wa.me/91'.$this->mobile;
    }

    public function scopeOfPartnerType($query, ?string $type)
    {
        return $query->when($type, fn ($q) => $q->where('partner_type', $type));
    }

    public function scopeOfStatus($query, ?string $status)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }
}
