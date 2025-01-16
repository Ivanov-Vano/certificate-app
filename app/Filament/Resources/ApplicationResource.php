<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'РЭЦ';
    protected static ?string $navigationLabel = 'заявки СПТ';
    protected static ?string $modelLabel = 'заявка';
    protected static ?string $pluralModelLabel = 'заявки';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return parent::getEloquentQuery()->count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->label('Номер')
                    ->hint('заявка на выдачу СПТ')
                    ->unique(ignoreRecord: true)
                    ->minLength(2)
                    ->maxLength(255),
                Select::make('type_id')
                    ->relationship('type', 'short_name')
                    ->searchable()
                    ->preload()
                    ->label('Тип СПТ'),
                Select::make('country_id')
                    ->relationship('country', 'short_name')
                    ->searchable()
                    ->preload()
                    ->label('Страна экспорта'),
                Fieldset::make('Заявитель')
                    ->schema([
                        Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Название'),
                        TextInput::make('phone')
                            ->label('Телефон'),
                        TextInput::make('email')
                            ->label('Почта'),
                    ]),
                Fieldset::make('Экспортер')
                    ->schema([
                        TextInput::make('inn')
                            ->label('ИНН'),
                        TextInput::make('exporter_name')
                            ->label('Название'),
                    ]),
                DateTimePicker::make('created_at')
                    ->label('Дата создания')
                    ->native(false)
                    ->displayFormat('d.m.Y h:i')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('Тип СПТ'),
                TextColumn::make('country.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('Страна экспорта'),
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->summarize(Count::make())
                    ->label('заявка на выдачу СПТ'),
                TextColumn::make('lead.name')
                    ->sortable()
                    ->searchable()
                    ->label('Заявитель'),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable()
                    ->label('Телефон заявителя'),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label('Почта заявителя:'),
                TextColumn::make('inn')
                    ->sortable()
                    ->searchable()
                    ->label('ИНН экспортера'),
                TextColumn::make('exporter_name')
                    ->sortable()
                    ->words(3)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->label('Название экспортера'),
                TextColumn::make('created_at')
                    ->label('создана запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('с'),
                        DatePicker::make('until')
                            ->label('по'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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

                SelectFilter::make('type_id')
                    ->label('Тип СПТ')
                    ->multiple()
                    ->preload()
                    ->relationship('type', 'short_name'),
                SelectFilter::make('country_id')
                    ->label('Страна экспорта')
                    ->multiple()
                    ->preload()
                    ->relationship('country', 'short_name'),
                SelectFilter::make('lead_id')
                    ->label('Заявитель')
                    ->multiple()
                    ->preload()
                    ->relationship('lead', 'name'),
            ])
            ->groups([
                Group::make('country.short_name')
                    ->getDescriptionFromRecordUsing(fn (Application $record): string => $record->country->name)
                    ->collapsible()
                    ->label('страна'),
                Group::make('type.short_name')
                    ->collapsible()
                    ->label('тип'),
                Group::make('inn')
                    ->collapsible()
                    ->label('ИНН'),
                Group::make('lead.name')
                    ->collapsible()
                    ->label('заявитель'),
                Group::make('exporter_name')
                    ->collapsible()
                    ->label('экспортер'),
                Group::make('created_at')
                    // Настройка сортировки по умолчанию
                    ->orderQueryUsing(
                        fn(Builder $query, string $direction) => $query->orderBy('created_at', 'desc'))
                    ->label('месяц')
                    ->getTitleFromRecordUsing(fn (Application $record): string => $record->created_at->format('m Y')),

            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageApplications::route('/'),
        ];
    }
}
