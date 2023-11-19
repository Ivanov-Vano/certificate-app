<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliverymanResource\Pages;
use App\Filament\Resources\DeliverymanResource\RelationManagers;
use App\Models\Deliveryman;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliverymanResource extends Resource
{
    protected static ?string $model = Deliveryman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $modelLabel = 'курьер';

    protected static ?string $pluralModelLabel = 'курьеры';

    protected static ?string $navigationGroup = 'Справочники';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ФИО')
                    ->description('Заполните данные курьера')
                    ->schema([
                        TextInput::make('surname')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->label('Фамилия'),
                        TextInput::make('name')
                            ->required()
                            ->label('Имя')
                            ->maxLength(255),
                        TextInput::make('patronymic')
                            ->label('Отчество')
                            ->maxLength(255),
                    ]),
                TextInput::make('full_name')
                    ->required()
                    ->label('ФИО')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Почта')
                    ->email()
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->sortable()->label('ФИО'),
                TextColumn::make('email')->label('Почта'),
                TextColumn::make('phone')->label('Телефон')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDeliverymen::route('/'),
            'create' => Pages\CreateDeliveryman::route('/create'),
            'edit' => Pages\EditDeliveryman::route('/{record}/edit'),
        ];
    }
}
