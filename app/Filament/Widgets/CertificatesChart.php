<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CertificatesChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Общая статистика';

    protected function getData(): array
    {
        $data = $this->getCertificatesPerMonth();
        return [
            'datasets' => [
                [
                    'label' => 'Количество',
                    'data' => $data['certificatesPerMonth']
                ],
            ],
            'labels' => $data['month'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getCertificatesPerMonth():array
    {

        $now = Carbon::now();

        $months = ['Янв','Фев','Мар','Апр','Май','Июнь','Июль','Авг','Сен','Окт','Ноя','Дек'];


        $certificatesPerMonth = collect(range(1,12))->map(function ($month) use($now) {

            return Certificate::query()->whereMonth('date', Carbon::parse($now->month($month)->format('Y-m')))->count();
        })->toArray();

        return [
            'certificatesPerMonth' => $certificatesPerMonth,
            'month' => $months
        ];
    }
}
