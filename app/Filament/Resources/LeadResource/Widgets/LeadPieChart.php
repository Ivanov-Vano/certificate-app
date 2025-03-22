<?php

namespace App\Filament\Resources\LeadResource\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadPieChart extends ChartWidget
{
    protected static ?string $heading = 'Рейтинг первых 10 Лидов по количеству заявок';

    protected function getData(): array
    {
        $leads = Lead::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(10)
            ->get();

        $labels = $leads->pluck('name')->toArray();
        $data = $leads->pluck('applications_count')->toArray();
        return [
            'datasets' => [
                [
                    'label' => 'Количество заявок у лидов',
                    'data' => $data
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
