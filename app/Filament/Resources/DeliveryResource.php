<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Actions\SetDeliveredAtDate;
use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Filament\Resources\Traits\HasTags;
use App\Models\Delivery;
use App\Models\Organization;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class DeliveryResource extends Resource
{
    use HasTags;

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
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                    $set('cost', Organization::find($state)?->delivery_price ?? 0))
                    ->label('Куда доставка'),
                Select::make('deliveryman_id')
                    ->relationship('deliveryman', 'full_name')
                    ->hidden(auth()->user()->hasRole(['Курьер']))
                    ->label('Курьер ФИО'),
                Toggle::make('is_pickup')
                    ->label('Самовывоз'),
                TextInput::make('cost')
                    ->numeric()
                    ->dehydrated()
                    ->readOnly(auth()->user()->hasRole(['Курьер']))
                    ->suffix('руб')
                    ->label('цена доставки'),
                DatePicker::make('delivered_at')
                    ->label('Доставлено'),
                self::formTagsField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        //получаем роль пользователя
        $roleName = auth()->user()->getRoleNames()->first();

        //пытаемся получить настройки из кэша (Значение кэшируется на 60 минут)
        $settings = Cache::remember('settings_'.$roleName, 60, function () use ($roleName) {
            //Если настроек не в кэше, загружаем их из базы данных
            return Setting::where('role_name', $roleName)->first()->columns_visibility ?? [];
        });

        return $table
            ->striped()
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('delivery_number', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_number', $settings))// проверка на присутствие в настройках
                    ->label('номер доставки'),
                TextColumn::make('accepted_at')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->visible(in_array('delivery_accepted_at', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_accepted_at', $settings))// проверка на присутствие в настройках
                    ->label('принято в доставку'),
                TextColumn::make('organization.short_name')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Delivery $record): string => $record->organization->address)
                    ->visible(in_array('delivery_organization_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_organization_short_name', $settings))// проверка на присутствие в настройках
                    ->label('получатель'),
                TextColumn::make('deliveryman.full_name')
                    ->sortable()
                    ->searchable()
//                    ->hidden(auth()->user()->hasRole(['Курьер']))
                    ->visible(in_array('delivery_deliveryman_full_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_deliveryman_full_name', $settings))// проверка на присутствие в настройках
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->label('курьер'),
                TextColumn::make('cost')
                    ->visible(in_array('delivery_cost', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_cost', $settings))// проверка на присутствие в настройках
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('стоимость'),
                TextColumn::make('certificates_count')
                    ->counts('certificates')
                    ->visible(in_array('delivery_certificates_count', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_certificates_count', $settings))// проверка на присутствие в настройках
                    ->label('количество передаваемых сертификатов'),
                IconColumn::make('is_pickup')
                    ->label('самовывоз')
                    ->boolean()
                    ->visible(in_array('delivery_is_pickup', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_is_pickup', $settings))// проверка на присутствие в настройках
                    ->summarize(
                        Count::make()->icons()),
                TextColumn::make('delivered_at')
                    ->label('дата и время доставки')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->visible(in_array('delivery_delivered_at', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('delivery_delivered_at', $settings))// проверка на присутствие в настройках
                    ->sortable(),
                self::tagsColumn(),
                TextColumn::make('deleted_at')
                    ->label('удалена запись')
//                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('создана запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->visible(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_number', $settings)),
                TextColumn::make('updated_at')
                    ->label('отредактирована запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->visible(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_number', $settings)),
            ])
            ->groups([
                Group::make('organization.short_name')->label('получатель'),
                Group::make('deliveryman.full_name')->label('курьер'),
/*                Group::make('delivered_at')
                    // Настройка сортировки по умолчанию
                    ->orderQueryUsing(
                        fn(Builder $query, string $direction) => $query->orderBy('delivered_at', 'desc'))
                    ->label('месяц')
                    ->getTitleFromRecordUsing(fn (Delivery $record): string => $record->delivered_at->format('m Y')), //todo  error format() on string
            ])
            ->defaultGroup('delivered_at')*/
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
                    ->relationship('deliveryman', 'full_name'),
                self::tagsFilter()
            ])
            ->actions([
                ViewAction::make(),
                SetDeliveredAtDate::make()->name('set_delivered_at_date')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    self::changeTagsAction(),
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
            'view' => Pages\ViewDelivery::route('/{record}'),
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
