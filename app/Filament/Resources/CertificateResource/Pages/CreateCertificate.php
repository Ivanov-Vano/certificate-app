<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Type;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use function Laravel\Prompts\select;

class CreateCertificate extends CreateRecord
{
    protected static string $resource = CertificateResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Создана новая заявка на сертификат';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $typeId = $data['type_id'];

        $priceArr = Type::query()
            ->select('price')
            ->whereKey($typeId)
            ->pluck('price')
            ->toArray();
        $price = array_values($priceArr)[0];
        if ($user->hasRole(['Эксперт'])) {
            $data['expert_id'] = $user->expert->id;
        }
        if ($data['scan_path'] !==null) {
            $data['cost'] = $price;
        }
        return $data;
    }
}
