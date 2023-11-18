<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
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
                TextColumn::make('expert.full_name')->sortable()->label('ФИО'),
                TextColumn::make('cost')
                    ->label('стоимость'),
            ]);
    }
}
