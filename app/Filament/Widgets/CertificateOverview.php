<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Expert;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CertificateOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Сертификаты', Certificate::query()->count())
                ->description('Всего')
                ->color('success'),
            Stat::make('Сертификаты', Certificate::query()->whereMonth('date', now())->count())
                ->description('Всего за текущий месяц')
                ->color('success'),

        ];
    }
}
