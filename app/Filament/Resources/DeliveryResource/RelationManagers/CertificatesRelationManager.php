<?php

namespace App\Filament\Resources\DeliveryResource\RelationManagers;

use App\Filament\Resources\CertificateResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificates';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $title = 'доставляемые заявки';

    protected static ?string $modelLabel = 'заявку на сертификат';

    protected static ?string $pluralModelLabel = 'заявки на сертификат';

   // нам нужно определить имя обратной связи, иначе оно будет искать множественное число
    protected static ?string $inverseRelationship = 'delivery';

    public function form(Form $form): Form
    {
        return CertificateResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->label('Номер'),
                TextColumn::make('date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label('дата'),
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('сертификат'),
                TextColumn::make('chamber.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('палата'),
                TextColumn::make('payer.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('плательщик'),
                TextColumn::make('sender.short_name')
                    ->sortable()
                    ->searchable()
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->label('откуда'),
                TextColumn::make('company.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('куда'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query
                        ->whereNull('delivery_id')// не в доставке
                        ->whereNotNull('scan_path'))// отправлены сканы
                    ->recordSelect(
                        fn (Select $select) => $select->placeholder('Выберите готовый сертификат для доставки'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
           ])
            ->emptyStateActions([
                CreateAction::make()
            ]);
    }
}
