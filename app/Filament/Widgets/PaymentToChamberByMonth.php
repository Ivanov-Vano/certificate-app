<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PaymentToChamberByMonth extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Статистика по палатам за месяц';

    protected function getHeading(): string
    {
        $month = request()->query('month', Carbon::now()->month);

        return 'Статистика по палатам за ' . ($month == Carbon::now()->month ? 'текущий' : 'предыдущий');
    }

    public function table(Table $table): Table
    {
        $month = request()->query('month', Carbon::now()->month);

        return $table
            ->query(
                CertificateResource::getEloquentQuery()
                    ->whereMonth('date', $month)
            )
            ->defaultPaginationPageOption(0)
            ->columns([
                TextColumn::make('cost_chamber')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('Cтоимость'),
            ])
            ->groups([
                Group::make('chamber.short_name')
                    ->label('ФИО')
                    ->collapsible(),
            ])
            ->defaultGroup('chamber.short_name')
            ->groupsOnly()
            ->filters([
                SelectFilter::make('month')
                    ->label('месяц')
                    ->options([
                        Carbon::now()->month => 'текущий',
                        Carbon::now()->subMonth()->month => 'предыдущий',
                    ])
                    ->default(Carbon::now()->month) // Установка фильтра по умолчанию за текущий месяц
                    ->query(function ($query, $data) {
                        $query->whereMonth('date', $data);
                    }),
            ]);
    }
}


