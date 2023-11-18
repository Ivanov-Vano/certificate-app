<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExpertResource;
use Filament\Tables;
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
                ExpertResource::getEloquentQuery()
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('full_name', 'asc')
            ->columns([
                TextColumn::make('full_name')->sortable()->label('ФИО'),
                TextColumn::make('certificates_count')
                    ->counts('certificates')
                    ->label('11'),
            ]);
    }
}
