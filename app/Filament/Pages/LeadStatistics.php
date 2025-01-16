<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LeadResource\Widgets\LeadOverview;
use App\Filament\Resources\LeadResource\Widgets\LeadPieChart;
use App\Filament\Resources\LeadResource\Widgets\LeadTable;
use Filament\Pages\Page;

class LeadStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $title = 'Статистика по лидам';

    protected static ?string $navigationGroup = 'РЭЦ';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.lead-statistics';

    protected function getHeaderWidgets(): array
    {
        return [
            LeadOverview::class,
            LeadPieChart::class,
            LeadTable::class
        ];
    }

}
