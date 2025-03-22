<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Expert;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CertificateOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель, Эксперт']);
    }

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $countAll = function ():string {
            if (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
                return Certificate::query()->count();
            }
            return Certificate::query()->whereBelongsTo(auth()->user()->expert)->count();
        };

        //количество за текущий месяц
        $countByMonthStat = function ():string {
            if (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
                return Certificate::query()
                    ->whereMonth('date', now())
                    ->count();
            }
            return Certificate::query()
                ->whereBelongsTo(auth()->user()->expert)
                ->whereMonth('date', now())
                ->count();
        };

        $countByMonthAll = Certificate::query()
            ->whereMonth('date', now())
            ->count();
        $countByMonthExpert = Certificate::query()
            ->whereBelongsTo(auth()->user()->expert)
            ->whereMonth('date', now())
            ->count();

        $countByMonth = (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) ? $countByMonthAll : $countByMonthExpert;

        //количество за предыдущий месяц
        $countByPreMonthStat = function ():string {
            if (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
                return Certificate::query()
                    ->whereMonth('date', ((Carbon::now()->month)-1))
                    ->count();
            }
            return Certificate::query()
                ->whereBelongsTo(auth()->user()->expert)
                ->whereMonth('date', ((Carbon::now()->month)-1))
                ->count();
        };


        $countByPreMonthAll = Certificate::query()
            ->whereMonth('date', ((Carbon::now()->month)-1))
            ->count();
        $countByPreMonthExpert = Certificate::query()
            ->whereBelongsTo(auth()->user()->expert)
            ->whereMonth('date', ((Carbon::now()->month)-1))
            ->count();

        $countByPreMonth = (auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) ? $countByPreMonthAll : $countByPreMonthExpert;

        $colorByMonth = $countByMonth >= $countByPreMonth ? 'success' : 'warning';
        $colorByPreMonth = $countByPreMonth > $countByMonth ? 'success' : 'warning';

        return [
            Stat::make('Сертификаты', $countAll)
                ->description('Всего')
                ->color('success'),
            Stat::make('Сертификаты', $countByMonthStat)
                ->description('Всего за текущий месяц')
                ->color($colorByMonth),
            Stat::make('Сертификаты', $countByPreMonthStat)
                ->description('Всего за предыдущий месяц')
                ->color($colorByPreMonth),

        ];
    }
}
