<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{

    protected static string $resource = LeadResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'все' => Tab::make('Все статусы лидов'),
            'новый' => Tab::make('новый')
                ->icon('heroicon-m-sparkles')
                ->badge(Lead::query()->where('status', '=', 'новый')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', '=', 'новый')),
            'в работе' => Tab::make('в работе')
                ->icon('heroicon-m-chat-bubble-left-ellipsis')
                ->badge(Lead::query()->where('status', '=', 'в работе')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', '=', 'в работе')),
            'успех' => Tab::make('успех')
                ->icon('heroicon-m-check-badge')
                ->badge(Lead::query()->where('status', '=', 'успех')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', '=', 'успех')),
            'отказ' => Tab::make('отказ')
                ->icon('heroicon-m-hand-thumb-down')
                ->badge(Lead::query()->where('status', '=', 'отказ')->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', '=', 'отказ')),
        ];
    }


}
