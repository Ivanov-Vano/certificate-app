<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
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
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'сертификат';

    protected static ?string $pluralModelLabel = 'сертификаты';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            return static::getModel()::count();
        }
        return static::getEloquentQuery()
            ->whereBelongsTo(auth()->user()->expert)->count();
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
                Select::make('sign_id')
                    ->relationship('sign', 'name')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->maxLength(255)
                            ->required()
                            ->label('Наименование'),
                    ])
                    ->label('Признак сертификата'),
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
                Select::make('expert_id')
                    ->relationship('expert', 'full_name')
                    ->required()
                    ->hidden(auth()->user()->hasRole(['Эксперт']))
                    ->label('Эксперт ФИО'),
                TextInput::make('extended_page')
                    ->numeric()
                    ->label('Дополнительные листы'),
                FileUpload::make('scan_path')
                    ->directory('attachments')
                    ->openable()
                    ->acceptedFileTypes(['application/pdf'])
                    ->label('Скан сертификата'),
                Section::make('Счет')
                    ->schema([
                        Toggle::make('invoice_issued')
                            ->label('Выставлен'),
                        Toggle::make('paid')
                            ->label('Оплачен')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->label('Номер заявки'),
                TextColumn::make('date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label('дата'),
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('сертификат'),
                TextColumn::make('sign.name')
                    ->sortable()
                    ->searchable()
                    ->label('признак'),
                TextColumn::make('chamber.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('палата'),
                TextColumn::make('payer.short_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
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
                TextColumn::make('company.country.short_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->label('страна получателя'),
                TextColumn::make('scan_path')
                    ->label('скан')
                    ->badge()
                    ->getStateUsing(fn (Certificate $record): string => $record->scan_path == null ? '' : 'Выдан')
                    ->colors([
                        'success' => 'Выдан',
                    ]),
                TextColumn::make('expert.full_name')
                    ->sortable()
                    ->searchable()
                    ->hidden(auth()->user()->hasRole(['Эксперт']))
                    ->words(1)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('эксперт'),
                IconColumn::make('invoice_issued')
                    ->label('счет выставлен')
                    ->boolean()
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
                    ->action(function($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    }),
                TextColumn::make('delivery_id')
                    ->label('статус доставки')
                    ->badge()
                    ->getStateUsing(fn (Certificate $record): string => $record->delivery_id == null ? '' : 'Доставлен')
                    ->colors([
                        'success' => 'Доставлен',
                    ]),
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
                    ->toggleable(isToggledHiddenByDefault: true)
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
                SelectFilter::make('type_id')
                    ->label('Тип сертификата')
                    ->multiple()
                    ->preload()
                    ->relationship('type', 'short_name'),
                SelectFilter::make('sign_id')
                    ->label('Признак сертификата')
                    ->preload()
                    ->relationship('sign', 'name'),
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
                    ->relationship('expert', 'full_name')
            ])
            ->groups([
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
                    ExportBulkAction::make(),
/*                    BulkAction::make('updateDelivery')
                        ->form([
                            Select::make('delivery_id')
                                ->label('Доставка')
                                ->options(Delivery::query()->pluck('number', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records):void {
                            $records->each->delivery_id = $data['delivery_id'];
                            dd($data['delivery_id']);
                            $records->save();
                        })*/
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

        if ($user->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель'])) {
            return parent::getEloquentQuery()/*->orderByDesc('date')*/;
        }
        return parent::getEloquentQuery()
            ->whereBelongsTo(auth()->user()->expert)
            ->orderByDesc('date');
    }
}
