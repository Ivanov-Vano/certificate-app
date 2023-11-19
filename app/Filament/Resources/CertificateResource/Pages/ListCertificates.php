<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Certificate;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class ListCertificates extends ListRecords
{
    protected static string $resource = CertificateResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все сертификаты'),
            'scan_issued' => Tab::make('Сканы не отправлены')
                ->badge(Certificate::query()->whereNull('scan_path')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('scan_issued', false)),
            'invoice_issued' => Tab::make('Счета не выставлены')
                ->badge(Certificate::query()->where('invoice_issued', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('invoice_issued', false)),
            'paid' => Tab::make('Счета не оплачены')
                ->badge(Certificate::query()->where('paid', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('paid', false)),
            'delivery_id' => Tab::make('Сертификаты не доставлены')
                ->badge(Certificate::query()->whereNull('delivery_id')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('delivery_id')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
