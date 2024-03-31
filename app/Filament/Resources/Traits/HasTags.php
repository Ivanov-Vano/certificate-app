<?php

namespace App\Filament\Resources\Traits;

use App\Models\Tag;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\TextInput;

trait HasTags
{
    public static function formTagsField() : Select
    {
        return self::tagsField()
            ->label('Ключевые слова')
            ->relationship('tags', 'name');
    }

    public static function tagsField() : Select
    {
        return Select::make('tags')
            ->options(Tag::pluck('name', 'id'))
            ->multiple()
            ->label('Ключевые слова')
            ->searchable()
            ->preload()
            ->createOptionForm([
                TextInput::make('name')
                    ->lazy()
                    ->label('Наименование')
                    ->afterStateUpdated(fn ($set, $state) => $set('name', ucfirst($state)))
                    ->required(),
            ]);
    }

    public static function changeTagsAction() : BulkAction
    {
        return BulkAction::make('change_tags')
            ->label('Изменить ключевые слова')
            ->icon('heroicon-o-tag')
            ->action(function (Collection $records, array $data): void {
                foreach ($records as $record) {
                    $record->tags()->{$data['action']}($data['tags']);
                }
            })
            ->form([
                Grid::make()
                    ->schema([
                        Select::make('action')
                            ->label('Для выбранных записей')
                            ->options([
                                'attach' => 'добавить',
                                'detach' => 'удалить',
                                'sync' => 'перезаписать',
                            ])
                            ->default('sync')
                            ->required(),

                        self::tagsField(),

                    ])->columns(2)
            ]);
    }

    public static function tagsColumn() : TextColumn
    {
        return TextColumn::make('tags.name')
            ->label('Ключевые слова')
            ->badge()
            ->separator(',')
            ->limit(3);
    }

    public static function tagsFilter(): SelectFilter
    {
        return SelectFilter::make('tags')
            ->label('Ключевые слова')
            ->multiple()
            ->preload()
            ->relationship('tags', 'name');
    }
}
