<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ChamberImporter;
use App\Filament\Resources\ChamberResource\Pages;
use App\Filament\Resources\ChamberResource\RelationManagers;
use App\Models\Chamber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChamberResource extends Resource
{
    protected static ?string $model = Chamber::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $modelLabel = 'палата';

    protected static ?string $pluralModelLabel = 'палаты';

    protected static ?string $navigationGroup = 'Справочники';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('short_name')
                    ->required()
                    ->label('Краткое наименование')
                    ->maxLength(100),
                Forms\Components\TextInput::make('name')
                    ->label('Наименование')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(50),
                Forms\Components\TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('short_name')
                    ->label('Краткое наименование')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ChamberImporter::class)
            ]);    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChambers::route('/'),
            'create' => Pages\CreateChamber::route('/create'),
            'edit' => Pages\EditChamber::route('/{record}/edit'),
        ];
    }
}
