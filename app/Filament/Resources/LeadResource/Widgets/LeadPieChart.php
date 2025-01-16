<?php

namespace App\Filament\Resources\LeadResource\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadPieChart extends ChartWidget
{
    protected static ?string $heading = 'Круговая диаграмма количества заявок СПТ по лидам';

    protected function getData(): array
    {
        $leads = Lead::withCount('applications')->get();

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
