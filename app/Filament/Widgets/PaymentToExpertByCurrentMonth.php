<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Carbon\Carbon;
use Filament\Forms\Components\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PaymentToExpertByCurrentMonth extends BaseWidget
{

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Статистика по экспертам за месяц';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                CertificateResource::getEloquentQuery()
                ->whereMonth('date', ((Carbon::now()->month)-1))
            )

            ->defaultPaginationPageOption(0)
            ->columns([
                TextColumn::make('cost')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('Cтоимость'),
            ])
            ->groups([
                Group::make('expert.full_name')
                    ->label('ФИО')
                    ->collapsible(),
            ])
           ->defaultGroup('expert.full_name')
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
