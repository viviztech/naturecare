<?php

namespace App\Filament\Widgets;

use App\Enums\PartnerType;
use App\Models\BusinessEnquiry;
use Filament\Widgets\ChartWidget;

class EnquiriesByPartnerTypeChart extends ChartWidget
{
    protected ?string $heading = 'Enquiries by Partner Type';

    protected function getData(): array
    {
        $counts = BusinessEnquiry::query()
            ->selectRaw('partner_type, count(*) as total')
            ->groupBy('partner_type')
            ->pluck('total', 'partner_type');

        $types = PartnerType::cases();

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => collect($types)->map(fn (PartnerType $type) => $counts[$type->value] ?? 0)->all(),
                    'backgroundColor' => ['#16a34a', '#0891b2', '#22c55e'],
                ],
            ],
            'labels' => collect($types)->map(fn (PartnerType $type) => $type->label())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
