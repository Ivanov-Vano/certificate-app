<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $pluralModelLabel = 'заявки СПТ';


    public function form(Form $form): Form
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
                Fieldset::make('Экспортер')
                    ->schema([
                        TextInput::make('inn')
                            ->label('ИНН'),
                        TextInput::make('exporter_name')
                            ->label('Название'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('type.short_name')
                    ->sortable()
                    ->searchable()
                    ->label('Тип СПТ'),
                TextColumn::make('country.name')
                    ->sortable()
                    ->searchable()
                    ->label('Страна экспорта'),
                TextColumn::make('number')
                    ->sortable()
                    ->searchable()
                    ->summarize(Count::make())
                    ->label('заявка на выдачу СПТ'),
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
            ->groups([
                Group::make('country.short_name')
                    ->getDescriptionFromRecordUsing(fn (Application $record): string => $record->country->name ?? '')
                    ->collapsible()
                    ->label('страна'),
                Group::make('type.short_name')
                    ->collapsible()
                    ->label('тип'),
            ])
            ->defaultGroup('country.short_name');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        // Получаем количество заявок СПТ для текущего заявителя
        $count = $ownerRecord->applications->count();
        return __('заявок СПТ (' . $count . ')' );
    }

}
