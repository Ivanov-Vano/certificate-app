<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PaymentToExpertByCurrentMonth extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Эксперты';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                CertificateResource::getEloquentQuery()
            )

            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->sortable()
                    ->label('Дата'),
                TextColumn::make('cost')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('стоимость'),
            ])
            ->groups([
                Group::make('expert.full_name')
                    ->label('ФИО'),
            ])
            ->defaultGroup('expert.full_name');
    }
}
