<?php

namespace App\Filament\Imports;

use App\Models\Chamber;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ChamberImporter extends Importer
{
    protected static ?string $model = Chamber::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('short_name')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->label('Наименование')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->rules(['max:50']),
            ImportColumn::make('address')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Chamber
    {
         return Chamber::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
             'short_name' => $this->data['short_name'],
         ]);

//        return new Chamber();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your chamber import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
