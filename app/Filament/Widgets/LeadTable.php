<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class LeadTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Потенциальные заказчики и их заявки СПТ';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->withCount('applications')
                    ->orderByDesc('applications_count')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('наименование')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('applications.phone')
                    ->label('Телефон')
                    ->getStateUsing(fn (Lead $record): string => $record->applications
                        ->pluck('phone')
                        ->unique()
                        ->implode(', ')),
                TextColumn::make('applications.email')
                    ->label('Электронная почта')
                    ->getStateUsing(fn (Lead $record): string => $record->applications
                        ->pluck('email')
                        ->unique()
                        ->implode(', ')),
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


            ]);
    }
}
