<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Filament\Resources\LeadResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'РЭЦ';
    protected static ?string $navigationLabel = 'Потенциальные заказчики';
    protected static ?string $modelLabel = 'лид';
    protected static ?string $pluralModelLabel = 'лиды';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return parent::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('наименование')
                    ->readOnly()
                    ->hint('только для чтения')
                    ->required(),
                TagsInput::make('aliases')
                    ->label('Алиасы')
                    ->separator(','),
                Textarea::make('note')
                    ->label('Примечание'),
                ToggleButtons::make('status')
                    ->inline()
                    ->label('Статус')
                    ->options([
                        'новый' => 'новый',
                        'в работе' => 'в работе',
                        'успех' => 'успех',
                        'отказ' => 'отказ',
                    ])
                    ->colors([
                        'новый' => 'info',
                        'в работе' => 'primary',
                        'успех' => 'success',
                        'отказ' => 'danger',
                    ])
                    ->icons([
                        'новый' => 'heroicon-m-sparkles',
                        'в работе' => 'heroicon-m-chat-bubble-left-ellipsis',
                        'успех' => 'heroicon-m-check-badge',
                        'отказ' => 'heroicon-m-hand-thumb-down',
                    ])
                    ->required(),
                TextInput::make('emails')
                    ->label('Электронные адреса')
                    ->disabled()
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record instanceof Lead) {
                            $component->state($record->emails);
                        }
                    }),
                TextInput::make('phones')
                    ->label('Телефоны')
                    ->disabled()
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record instanceof Lead) {
                            $component->state($record->phones);
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('наименование')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('aliases')
                    ->visible(false)
                    ->label('Алиасы')
                    ->searchable(),
                TextColumn::make('applications')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->joinRelationship('applications')
                            ->select('leads.*', DB::raw('COUNT(applications.id) as applications_count'))
                            ->groupBy('leads.id')
                            ->orderBy('applications_count', $direction);
                    })
                    ->label('Кол-во заявок СПТ')
                    ->getStateUsing(fn (Lead $record): string => $record->applications()->count() ?? 0),
                TextColumn::make('status')
                    ->badge()
                    ->label('статус')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Комментарии')
                    ->words(3)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
            ])

            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            RelationManagers\ApplicationsRelationManager::make(),
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
            'view' => Pages\ViewLead::route('/{record}/view'),
            'edit-status' => Pages\EditLeadStatus::route('/{record}/edit/status'),
        ];
    }
}
