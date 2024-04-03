<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Filament\Resources\Traits\HasTags;
use App\Models\Certificate;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Grouping\Group;

class CertificateResource extends Resource
{
    use HasTags;

    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'сертификат';

    protected static ?string $pluralModelLabel = 'сертификаты';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasAnyRole(['Представитель палаты', 'Эксперт'])) {
            $relatedModel = $user->hasRole('Представитель палаты') ? $user->chamber : $user->expert;
            $query->whereBelongsTo($relatedModel)->count();
        } elseif (!$user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            // любые дополнительные ограничения для пользователей без необходимых ролей
        }
        return $query->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->required()
                    ->readOnly()
                    ->label('Номер заявки'),
                DatePicker::make('date')
                    ->required()
                    ->label('Дата'),

                Select::make('type_id')
                    ->autofocus()
                    ->relationship('type', 'short_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('short_name')
                            ->maxLength(100)
                            ->required()
                            ->label('Наименование'),
                        TextInput::make('name')
                            ->maxLength(255)
                            ->label('Полное наименование'),
                    ])
                    ->label('Тип сертификата'),
                Select::make('chamber_id')
                    ->relationship('chamber', 'short_name')
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('short_name')
                            ->maxLength(100)
                            ->required()
                            ->label('Наименование'),
                        TextInput::make('name')
                            ->maxLength(255)
                            ->label('Полное наименование'),
                    ])
                    ->label('Торгово-промышленная палата'),
                Select::make('payer_id')
                    ->relationship('payer', 'short_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('short_name')
                            ->maxLength(100)
                            ->required()
                            ->label('Наименование'),
                        TextInput::make('name')
                            ->maxLength(255)
                            ->label('Полное наименование'),
                    ])
                    ->label('Организация (плательщик)'),
                Select::make('sender_id')
                    ->relationship('sender', 'short_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('short_name')
                            ->maxLength(100)
                            ->required()
                            ->label('Наименование'),
                        TextInput::make('name')
                            ->maxLength(255)
                            ->label('Полное наименование'),
                    ])
                    ->label('Организация (откуда)'),
                Select::make('company_id')
                    ->relationship('company', 'short_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Select::make('country_id')
                            ->relationship('country', 'short_name')
                            ->required()
                            ->label('Страна'),
                        TextInput::make('short_name')
                            ->maxLength(100)
                            ->required()
                            ->label('Наименование'),
                        TextInput::make('name')
                            ->maxLength(255)
                            ->label('Полное наименование'),
                    ])
                    ->label('Организация (куда)'),
                TextInput::make('transfer_document')
                    ->label('УПД'),
                TextInput::make('agreement')
                    ->label('Согласование'),
                Select::make('expert_id')
                    ->relationship('expert', 'full_name')
                    ->required()
                    ->hidden(auth()->user()->hasRole(['Эксперт']))
                    ->label('Эксперт ФИО'),
                TextInput::make('extended_page')
                    ->numeric()
                    ->label('Дополнительные листы'),
                self::formTagsField(),
                Section::make([
                        Section::make('Признаки')
                            ->schema([
                                Toggle::make('rec')
                                    ->label('РЭЦ'),
                                Toggle::make('second_invoice')
                                    ->label('2с/ф')
                            ])->columns((['md' => 1, 'lg' => 2])),
                        Section::make('Счет')
                            ->schema([
                                Toggle::make('invoice_issued')
                                    ->label('Выставлен'),
                                Toggle::make('paid')
                                    ->label('Оплачен')
                            ])->columns((['md' => 1, 'lg' => 2])),
                    ]),
                FileUpload::make('scan_path')
                    ->directory('attachments')
                    ->openable()
                    ->acceptedFileTypes(['application/pdf'])
                    ->label('Скан сертификата'),
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
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_number', $settings))// проверка на присутствие в настройках
                    ->label('номер заявки'),
                TextColumn::make('date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->visible(in_array('certificate_date', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_date', $settings))// проверка на присутствие в настройках
                    ->label('дата'),
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_type_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_type_short_name', $settings))// проверка на присутствие в настройках
                    ->label('тип сертификата'),
                TextColumn::make('rec')
                    ->label('признак РЭЦ')
                    ->badge()
                    ->visible(in_array('certificate_rec', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_rec', $settings))// проверка на присутствие в настройках
                    ->getStateUsing(fn (Certificate $record): string => $record->rec ? 'РЭЦ' : '')
                    ->colors([
                        'success' => 'РЭЦ',
                    ]),
                IconColumn::make('second_invoice')
                    ->label('признак 2с/ф')
                    ->boolean()
                    ->visible(in_array('certificate_second_invoice', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_second_invoice', $settings))// проверка на присутствие в настройках
                    ->action(function($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    }),
                TextColumn::make('chamber.short_name')
                    ->sortable()
                    ->visible(in_array('certificate_chamber_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_chamber_short_name', $settings))// проверка на присутствие в настройках
                    ->searchable()
                    ->label('палата'),
                TextColumn::make('payer.short_name')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_payer_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_payer_short_name', $settings))// проверка на присутствие в настройках
                    ->label('плательщик'),
                TextColumn::make('sender.short_name')
                    ->sortable()
                    ->searchable()
                    ->words(1)
                    ->visible(in_array('certificate_sender_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_sender_short_name', $settings))// проверка на присутствие в настройках
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('откуда'),
                TextColumn::make('company.short_name')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_company_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_company_short_name', $settings))// проверка на присутствие в настройках
                    ->label('куда'),
                TextColumn::make('transfer_document')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_transfer_document', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_transfer_document', $settings))// проверка на присутствие в настройках
                    ->label('УПД'),
                TextColumn::make('company.country.short_name')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_company_country_short_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_company_country_short_name', $settings))// проверка на присутствие в настройках
                    ->label('страна получателя'),
                TextColumn::make('agreement')
                    ->sortable()
                    ->searchable()
                    ->visible(in_array('certificate_agreement', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_agreement', $settings))// проверка на присутствие в настройках
                    ->label('согласование'),
                TextColumn::make('scan_path')
                    ->label('скан')
                    ->badge()
                    ->visible(in_array('certificate_scan_path', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_scan_path', $settings))// проверка на присутствие в настройках
                    ->getStateUsing(fn (Certificate $record): string => $record->scan_path == null ? '' : 'Выдан')
                    ->colors([
                        'success' => 'Выдан',
                    ]),
                TextColumn::make('expert.full_name')
                    ->sortable()
                    ->searchable()
//                    ->hidden(auth()->user()->hasRole(['Эксперт']))
                    ->visible(in_array('certificate_expert_full_name', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_expert_full_name', $settings))// проверка на присутствие в настройках
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('эксперт'),
                IconColumn::make('invoice_issued')
                    ->label('счет выставлен')
                    ->boolean()
                    ->visible(in_array('certificate_invoice_issued', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_invoice_issued', $settings))// проверка на присутствие в настройках
                    ->action(function($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    }),

                IconColumn::make('paid')
                    ->label('счет оплачен')
/*                    ->summarize(
                        Count::make()->query(fn (Builder $query) => $query->where('paid', true)),
                    )*/ // todo Argument #1 ($query) must be of type Illuminate\Database\Eloquent\Builder, Illuminate\Database\Query\Builder given
                    ->boolean()
                    ->visible(in_array('certificate_paid', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_paid', $settings))// проверка на присутствие в настройках
                    ->action(function($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    }),
                TextColumn::make('delivery_id')
                    ->label('статус доставки')
                    ->badge()
                    ->visible(in_array('certificate_delivery_id', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_delivery_id', $settings))// проверка на присутствие в настройках
                    ->getStateUsing(function (Certificate $record): string {
                        // Проверяем, связана ли запись Certificate с Delivery
                        if ($record->delivery_id === null) {
                            // Если delivery_id не установлен, значит доставка не назначена
                            return 'Не доставлен';
                        }

                        // Получаем связанную запись Delivery
                        $delivery = $record->delivery;

                        // Проверяем, была ли доставка выполнена
                        if ($delivery && $delivery->delivered_at) {
                            // Если установлено delivered_at, значит доставка завершена
                            return 'Доставлен';
                        } elseif ($delivery && $delivery->accepted_at) {
                            // Если установлено accepted_at, но не delivered_at, значит доставка в процессе
                            return 'В процессе';
                        } else {
                            // Если ни одно из условий не выполнено, значит доставка не назначена
                            return 'Не доставлен';
                        }
                    })
                    ->colors([
                        'success' => 'Доставлен',    // Зеленый цвет для статуса "Доставлен"
                        'warning' => 'В процессе',   // Желтый цвет для статуса "В процессе"
                        'danger' => 'Не доставлен',  // Красный цвет для статуса "Не доставлен"
                    ]),
                self::tagsColumn(),
                TextColumn::make('deleted_at')
                    ->label('удалена запись')
//                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(in_array('certificate_deleted_at', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_deleted_at', $settings))// проверка на присутствие в настройках
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('создана запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->visible(in_array('certificate_created_at', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_created_at', $settings))// проверка на присутствие в настройках
//                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('отредактирована запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->visible(in_array('certificate_updated_at', $settings))// проверка на присутствие в настройках
                    ->toggleable(in_array('certificate_updated_at', $settings))// проверка на присутствие в настройках
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('с'),
                        DatePicker::make('until')
                            ->label('по')/*
                            ->default(now())*/,
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
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

                Filter::make('delivery_id')
                    ->label('Доставка')
                    ->form([
                        Checkbox::make('is_delivered')
                            ->label('доставлено'),
                        Checkbox::make('is_not_delivered')
                            ->label('не доставлено'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['is_delivered'],
                                fn (Builder $query): Builder =>  $query->whereNotNull('delivery_id')
                            )
                            ->when(
                                $data['is_not_delivered'],
                                fn (Builder $query): Builder => $query->orWhereNull('delivery_id')
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['is_delivered'] ?? null) {
                            $indicators[] = Indicator::make('доставлено')
                                ->removeField('is_delivered');
                        }

                        if ($data['is_not_delivered'] ?? null) {
                            $indicators[] = Indicator::make('не доставлено')
                                ->removeField('is_not_delivered');
                        }
                        return $indicators;
                    }),
                Filter::make('scan_path')
                    ->toggle()
                    ->label('Скан отправлен')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('scan_path')),
                Filter::make('invoice_issued')
                    ->toggle()
                    ->label('Счет выставлен')
                    ->query(fn (Builder $query): Builder => $query->where('invoice_issued', true)),
                Filter::make('paid')
                    ->toggle()
                    ->label('Счет оплачен')
                    ->query(fn (Builder $query): Builder => $query->where('paid', true)),
                Filter::make('rec')
                    ->toggle()
                    ->label('Признак РЭЦ')
                    ->query(fn (Builder $query): Builder => $query->where('rec', true)),
                Filter::make('second_invoice')
                    ->toggle()
                    ->label('Признак 2с/ф')
                    ->query(fn (Builder $query): Builder => $query->where('second_invoice', true)),
                SelectFilter::make('type_id')
                    ->label('Тип сертификата')
                    ->multiple()
                    ->preload()
                    ->relationship('type', 'short_name'),
                SelectFilter::make('chamber_id')
                    ->label('Палата')
                    ->multiple()
                    ->preload()
                    ->relationship('chamber', 'short_name'),
                SelectFilter::make('payer_id')
                    ->label('Плательщик')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship('payer', 'short_name'),
                SelectFilter::make('sender_id')
                    ->label('Откуда')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship('sender', 'short_name'),
                SelectFilter::make('company_id')
                    ->label('Куда')
                    ->multiple()
                    ->preload()
                    ->relationship('company', 'short_name'),
                SelectFilter::make('expert_id')
                    ->label('Эксперт')
                    ->multiple()
                    ->preload()
                    ->relationship('expert', 'full_name'),
                self::tagsFilter()
            ])
            ->groups([
                Group::make('sender.short_name')->label('откуда'),
                Group::make('type.short_name')->label('тип'),
                Group::make('chamber.short_name')->label('палата'),
                Group::make('date')
                    // Настройка сортировки по умолчанию
                    ->orderQueryUsing(
                        fn(Builder $query, string $direction) => $query->orderBy('date', 'desc'))
                    ->label('месяц')
                    ->getTitleFromRecordUsing(fn (Certificate $record): string => $record->date->format('m Y')),
            ])
            ->defaultGroup('date')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Копирование сертификата')
                            ->body('Заявка на сертификат была скопирована успешно!'),
                    ),
/*                    ->excludeAttributes(['number', 'date'])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $user = auth()->user();
                        $year = now()->format('Y');

                        // генерируем новый номер заявки
                        // формат номера: 20ХХ/ХХХХ
                        $lastNumber = Certificate::query()->select('number')->orderBy('number')->get();
                        $last = $lastNumber->pluck('number')->last();
                        $last = $last ?? $year.'/0001';   //если пусто, то первый номер: 20ХХ/0001
                        $number = (substr($last,(strpos($last, '/')+1)) + 1);
                        switch (strlen($number)) {
                            case 1:
                                $number = '000'.$number;
                                break;
                            case 2:
                                $number = '00'.$number;
                                break;
                            case 3:
                                $number = '0'.$number;
                                break;
                            case 4:
                                $number = ''.$number;
                                break;
                        }
                        $number = $year.'/'.$number;
                        $data['number'] = $number;

                        // генерируем дату заявки
                        $data['date'] = now();

                        return $data;
                    }),*/
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()->label('Экспорт выбранных записей'),
                    self::changeTagsAction(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->label('Экспорт'),
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()/*->orderByDesc('date')*/;

        if ($user->hasAnyRole(['Представитель палаты', 'Эксперт'])) {
            $relatedModel = $user->hasRole('Представитель палаты') ? $user->chamber : $user->expert;
            $query->whereBelongsTo($relatedModel);
        } elseif (!$user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            // любые дополнительные ограничения для пользователей без необходимых ролей
        }

        return $query;
    }
}
