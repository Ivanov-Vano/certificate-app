<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Carbon\Carbon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Log;

class PaymentToExpertByCurrentMonth extends BaseWidget
{

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Статистика по экспертам за месяц';

    public static function canView():bool
    {
        return auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель', 'Эксперт']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CertificateResource::getEloquentQuery()
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
                        Carbon::now()->startOfMonth()->month => 'текущий',
                        Carbon::now()->subMonth()->startOfMonth()->month => 'предыдущий',
                    ])
                    ->default(Carbon::now()->startOfMonth()->month)
                    ->query(function ($query, $data) {
                        $query->whereMonth('date', $data);
                    }),
            ]);
    }
}
