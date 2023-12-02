<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Filament\Resources\OrganizationResource\RelationManagers;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'заявитель/экспортер';

    protected static ?string $pluralModelLabel = 'заявители/экспортеры';

    protected static ?string $navigationGroup = 'Справочники';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make([
                            TextInput::make('short_name')
                                ->label('Наименование')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('inn')
                                ->label('ИНН')
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                            TextInput::make('name')
                                ->label('Полное наименование')
                                ->columnSpanFull()
                                ->maxLength(255),
                            TextInput::make('address')
                                ->label('адрес')
                                ->columnSpanFull()
                                ->maxLength(255),
                        ])
                            ->columns(2),
                        Section::make('Телефон')
                            ->schema([
                                TextInput::make('phone')
                                    ->label('номер')
                                    ->tel()
                                    ->maxLength(50),
                                TextInput::make('additional_number')
                                    ->label('добавочный номер')
                                    ->maxLength(50),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Стоимость')
                        ->schema([
                            TextInput::make('delivery_price')
                                ->numeric()
                                ->suffix('руб')
                                ->label('цена доставки'),
                        ])
                ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('short_name')
                    ->label('Наименование')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Полное наименование')
                    ->searchable(),
                TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('additional_number')
                    ->label('Добавочный номер')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлена')
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
            RelationManagers\ClientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
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
