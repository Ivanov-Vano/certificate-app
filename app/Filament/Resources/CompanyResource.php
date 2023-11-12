<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $modelLabel = 'компания';

    protected static ?string $pluralModelLabel = 'компании';

    protected static ?string $navigationGroup = 'Справочники';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('country_id')
                    ->autofocus()
                    ->relationship('country', 'short_name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label('Страна'),
                TextInput::make('short_name')
                    ->required()
                    ->label('Краткое наименование')
                    ->maxLength(100),
                TextInput::make('name')
                    ->label('Наименование')
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(255),
                TextInput::make('registration_number')
                    ->label('Регистрационный номер')
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.short_name')
                    ->numeric()
                    ->sortable()
                    ->label('Страна')
                    ->searchable(),
                Tables\Columns\TextColumn::make('short_name')
                    ->label('Краткое наименование')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Регистрационный номер')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
