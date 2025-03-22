<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CertificatesChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Общая статистика';

    public static function canView(): bool
    {
        if (auth()->user()->hasAnyRole(['Представитель палаты', 'Курьер'])) {
            return false;
        } else {
            return true;
        }
    }

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

    private function getCertificatesPerMonth(): array
    {
        $now = Carbon::now();
        $months = [];
        $certificatesPerMonth = [];

        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $now->copy()->subMonths($i);
            $monthName = $currentMonth->format('m.Y');
            $months[] = $monthName;

            if (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
                $count = Certificate::query()
                    ->whereMonth('date', $currentMonth->format('m'))
                    ->whereYear('date', $currentMonth->format('Y'))
                    ->count();
            } else {
                $count = Certificate::query()
                    ->whereBelongsTo(auth()->user()->expert)
                    ->whereMonth('date', $currentMonth->format('m'))
                    ->whereYear('date', $currentMonth->format('Y'))
                    ->count();
            }

            $certificatesPerMonth[] = $count;
        }

        // Перевернем массивы, чтобы получить правильный хронологический порядок.
        $months = array_reverse($months);
        $certificatesPerMonth = array_reverse($certificatesPerMonth);

        return [
            'certificatesPerMonth' => $certificatesPerMonth,
            'month' => $months
        ];
    }
}
