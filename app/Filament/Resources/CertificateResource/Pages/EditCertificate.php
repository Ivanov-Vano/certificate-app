<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Type;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCertificate extends EditRecord
{
    protected static string $resource = CertificateResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Изменена заявка на сертификат';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $typeId = $data['type_id'];

        $priceArr = Type::query()
            ->select('price')
            ->whereKey($typeId)
            ->pluck('price')
            ->toArray();
        $price = array_values($priceArr)[0];

        if ($data['scan_path'] !==null) {
            $data['cost'] = $price;
        }
        return $data;
    }
}
