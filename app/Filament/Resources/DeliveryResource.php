<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Certificate;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $modelLabel = 'доставка';

    protected static ?string $pluralModelLabel = 'доставки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->numeric()
                    ->label('Номер доставки'),
                DatePicker::make('accepted_at')
                    ->required()
                    ->label('Принято в доставку'),
                Select::make('organization_id')
                    ->relationship('organization', 'short_name')
                    ->required()
                    ->label('Куда доставка'),
                Select::make('deliverymen_id')
                    ->relationship('deliveryman', 'full_name')
                    ->hidden(auth()->user()->hasRole(['Курьер']))
                    ->label('Курьер ФИО'),
                Toggle::make('is_pickup')
                    ->label('Самовывоз'),
                DatePicker::make('delivered_at')
                    ->label('Доставлено'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->label('Номер'),
                TextColumn::make('accepted_at')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('Принято в доставку'),
                TextColumn::make('organization.short_name')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Delivery $record): string => $record->organization->address)
                    ->label('Куда'),
                TextColumn::make('deliveryman.full_name')
                    ->sortable()
                    ->searchable()
                    ->hidden(auth()->user()->hasRole(['Курьер']))
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('Курьер'),
                IconColumn::make('is_pickup')
                    ->label('Самовывоз')
                    ->boolean(),
                TextColumn::make('delivered_at')
                    ->label('Дата и время доставки')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('deleted_at')
                    ->label('удалена запись')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('создана запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('отредактирована запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }
}
