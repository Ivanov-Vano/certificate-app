<?php

namespace App\Filament\Resources\DeliveryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificates';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->maxLength(255),
            ]);
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
                        $state = $column->getState();
                        return $state;
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
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ]);
    }
}
