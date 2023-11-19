<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CertificateByExpertChart extends ChartWidget
{
    protected static ?string $heading = 'Статистика по экспертам';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        if (auth()->user()->hasRole(['Эксперт'])) {
            return false;
        } else {
            return true;
        }
    }
    protected function getData(): array
    {
        $data = Certificate::select('surname', DB::raw('count(*) as count'))
            ->join('experts', 'certificates.expert_id', '=', 'experts.id')
            ->groupBy('surname')
            ->pluck('count', 'surname')
            ->toArray();


        return [
            'datasets' => [
                [
                    'label' => 'Количество сертификатов эксперта',
                    'data' => array_values($data)
                ],
            ],
            'labels' => array_keys($data),
        ];

    }

    protected function getType(): string
    {
        return 'bar';
    }
}
