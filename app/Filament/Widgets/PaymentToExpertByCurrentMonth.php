<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Carbon\Carbon;
use Filament\Forms\Components\Builder;
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

    protected static ?string $heading = 'Статистика по экспертам за предыдущий месяц';


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
//            ->defaultGroup(
            ->groups([
                Group::make('expert.full_name')
                    ->label('ФИО')
                    ->collapsible(),
                // Группировка записей по Году и Месяцу (2023-09, 2023-11 etc) вместо поля "Дата"
/*                Group::make('date')
                    // Пример: "2023-10-0",
                    // Примечание:  Необходимо "-0" в конце, так Carbon мог спарсить дату.
                    ->getKeyFromRecordUsing(
                        fn(Certificate $record): string => $record->date->format('Y-m-d')
                    )

                    // Пример: "September 2023"
                    ->getTitleFromRecordUsing(
                        fn(Certificate $record): string => $record->date->format('F Y')
                    )

                    // Настройка сортировки по умолчанию
                    ->orderQueryUsing(
                        fn(Builder $query, string $direction) => $query->orderBy('date', 'desc')
                    )
                    // Скрыть "Дата:" в заголовке группировки
                    ->titlePrefixedWithLabel(false),*/
            ])
           ->defaultGroup('expert.full_name')
           ->groupsOnly();
    }
}
