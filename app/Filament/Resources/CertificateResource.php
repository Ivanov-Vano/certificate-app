<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Filament\Resources\CertificateResource\RelationManagers;
use App\Models\Certificate;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                Select::make('type_id')
                    ->autofocus()
                    ->relationship('type', 'short_name')
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
                    ->label('Торгово-промышленная палата'),
                Select::make('payer_id')
                    ->relationship('payer', 'short_name')
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
                    ->label('Эксперт ФИО'),
                TextInput::make('extended_page')
                    ->numeric()
                    ->label('Дополнительные листы'),
                Section::make('Статусы')
                    ->schema([
                        Toggle::make('scan_issued')
                            ->label('Выдан скан'),
                        Toggle::make('invoice_issued')
                            ->label('Счет выставлен'),
                        Toggle::make('paid')
                            ->label('Счет оплачен')
                    ]),
                FileUpload::make('scan_path')
                    ->directory('attachments')
                    ->preserveFilenames()
                    ->reactive()
//                    ->afterStateUpdated(fn ($state, callable $set) => $set('name', pathinfo($state->getClientOriginalName(), PATHINFO_FILENAME)))
                    ->label('Скан сертификата'),
                DatePicker::make('date')
                    ->required()
                    ->label('Дата'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->label('откуда'),
                TextColumn::make('company.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('куда'),
                TextColumn::make('expert.full_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('эксперт'),
                IconColumn::make('scan_issued')
                    ->label('выдан скан')
                    ->boolean(),
                IconColumn::make('invoice_issued')
                    ->label('счет выставлен')
                    ->boolean(),
                IconColumn::make('paid')
                    ->label('счет оплачен')
                    ->boolean(),
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
                //TrashedFilter::make(),
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('с'),
                        DatePicker::make('until')
                            ->label('по')
                            ->default(now()),
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

                Filter::make('scan_issued')
                    ->toggle()
                    ->label('Скан отправлен')
                    ->query(fn (Builder $query): Builder => $query->where('scan_issued', true)),
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
                    ->relationship('company', 'short_name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ExportBulkAction::make()
                ]),
            ])
            ->headerActions([
                ExportAction::make()

                    ->label('Экспорт'),
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
            return parent::getEloquentQuery()
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]);
        }
        return parent::getEloquentQuery()
            ->whereBelongsTo(auth()->user()->expert)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
