<?php

namespace App\Filament\Resources\LeadResource\Widgets;

use App\Models\Application;
use App\Models\Lead;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $countLeadsAll = function ():string {
            return Lead::query()->count();
        };
        $countApplicationsAll = function ():string {
            return Application::query()->count();
        };

        $countByMonthApplications = Application::query()
            ->whereMonth('created_at', now())
            ->count();

        $countByPreMonthApplications = Application::query()
            ->whereMonth('created_at', ((Carbon::now()->month)-1))
            ->count();

        $colorByMonth = $countByMonthApplications >= $countByPreMonthApplications ? 'success' : 'warning';

        return [
            Stat::make('Потенциальные заказчики', $countLeadsAll)
                ->description('Всего')
                ->color('success'),
            Stat::make('Заявки СПТ', $countApplicationsAll)
                ->description('Всего')
                ->color('success'),
            Stat::make('Заявки СПТ', $countByMonthApplications)
                ->description('Всего за текущий месяц')
                ->color($colorByMonth),
        ];
    }
}
