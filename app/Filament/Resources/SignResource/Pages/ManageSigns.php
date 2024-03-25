<?php

namespace App\Filament\Resources\SignResource\Pages;

use App\Filament\Resources\SignResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSigns extends ManageRecords
{
    protected static string $resource = SignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
