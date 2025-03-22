<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use Filament\Actions;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditLeadStatus extends EditRecord
{
    protected static string $resource = LeadResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ToggleButtons::make('status')
                    ->inline()
                    ->label('Статус')
                    ->options([
                        'New' => 'новый',
                        'Worked' => 'в работе',
                        'Completed' => 'успех',
                        'Canceled' => 'отказ',
                    ])
                    ->colors([
                        'New' => 'info',
                        'Worked' => 'primary',
                        'Completed' => 'success',
                        'Canceled' => 'danger',
                    ])
                    ->icons([
                        'New' => 'heroicon-m-sparkles',
                        'Worked' => 'heroicon-m-chat-bubble-left-ellipsis',
                        'Completed' => 'heroicon-m-check-badge',
                        'Canceled' => 'heroicon-m-hand-thumb-down',
                    ])
                    ->required(),
            ]);
    }
}
