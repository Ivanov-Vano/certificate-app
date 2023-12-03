<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PaymentToDeliverymenByMonth extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Статистика по курьерам за предыдущий месяц';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DeliveryResource::getEloquentQuery()
                    ->whereMonth('delivered_at', ((Carbon::now()->month)-1))
            )
            ->defaultSort('delivered_at', 'desc')
            ->columns([
                TextColumn::make('cost')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('Cтоимость'),
            ])
            ->groups([
                Group::make('deliveryman.full_name')
                    ->label('ФИО')
                    ->collapsible(),
            ])
            ->defaultGroup('deliveryman.full_name')
            ->groupsOnly();
    }
}
