<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Lead;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Дополнительно';
    protected static ?string $navigationLabel = 'Потенциальные заказчики';
    protected static ?string $modelLabel = 'лид';
    protected static ?string $pluralModelLabel = 'лиды';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('application_number')
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
                        TextInput::make('applicant')
                            ->label('Наименование'),
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
                TextColumn::make('application_number')
                    ->sortable()
                    ->searchable()
                    ->label('заявка на выдачу СПТ'),
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('Тип СПТ'),
                TextColumn::make('country.name')
                    ->sortable()
                    ->searchable()
                    ->label('Страна экспорта'),
                TextColumn::make('applicant')
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
                    ->searchable()
                    ->label('Название экспортера'),
                TextColumn::make('created_at')
                    ->label('создана запись')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
            ])
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
            'index' => Pages\ManageLeads::route('/'),
        ];
    }
}
