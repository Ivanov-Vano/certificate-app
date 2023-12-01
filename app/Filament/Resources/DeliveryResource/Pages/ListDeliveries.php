<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDeliveries extends ListRecords
{
    protected static string $resource = DeliveryResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все доставки'),
            'in delivery' => Tab::make('В доставке')
                ->badge(Delivery::query()->whereNull('delivered_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('delivered_at')),
            'delivered' => Tab::make('Доставлено')
                ->badge(Delivery::query()->whereNotNull('delivered_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('delivered_at')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
