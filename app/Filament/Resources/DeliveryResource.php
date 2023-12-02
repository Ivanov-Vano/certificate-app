<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Certificate;
use App\Models\Delivery;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $modelLabel = 'доставка';

    protected static ?string $pluralModelLabel = 'доставки';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            return static::getModel()::count();
        }
        return static::getEloquentQuery()
            ->whereBelongsTo(auth()->user()->deliveryman)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->readOnly()
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
                TextInput::make('cost')
                    ->numeric()
                    ->readOnly(auth()->user()->hasRole(['Курьер']))
                    ->suffix('руб')
                    ->label('цена доставки'),
                DatePicker::make('delivered_at')
                    ->label('Доставлено'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->label('Номер'),
                TextColumn::make('accepted_at')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->toggleable()
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
                        return $column->getState();
                    })
                    ->label('Курьер'),
                TextColumn::make('cost')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('Cтоимость'),
                TextColumn::make('certificates_count')
                    ->counts('certificates')
                    ->label('Количество передаваемых сертификатов'),
                IconColumn::make('is_pickup')
                    ->label('Самовывоз')
                    ->boolean()
                    ->summarize(
                        Count::make()->icons()),
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
                Filter::make('delivered_at')
                    ->form([
                        DatePicker::make('from')->label('с'),
                        DatePicker::make('until')
                            ->label('по'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivered_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivered_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('С ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('по ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('is_pickup')
                    ->toggle()
                    ->label('Самовывоз')
                    ->query(fn (Builder $query): Builder => $query->where('is_pickup', true)),
                SelectFilter::make('organization_id')
                    ->label('Получатель')
                    ->multiple()
                    ->preload()
                    ->relationship('organization', 'short_name'),
                SelectFilter::make('deliveryman_id')
                    ->label('Эксперт')
                    ->multiple()
                    ->preload()
                    ->relationship('deliveryman', 'full_name')
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
            RelationManagers\CertificatesRelationManager::class,
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
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()
            ->whereBelongsTo(auth()->user()->deliveryman);
    }
}
