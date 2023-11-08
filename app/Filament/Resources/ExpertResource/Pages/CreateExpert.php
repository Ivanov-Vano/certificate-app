<?php

namespace App\Filament\Resources\ExpertResource\Pages;

use App\Filament\Resources\ExpertResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpert extends CreateRecord
{
    protected static string $resource = ExpertResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Создан новый эксперт';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
