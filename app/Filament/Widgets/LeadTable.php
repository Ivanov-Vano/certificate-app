<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

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
                    ->words(3)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state;
                    })
                    ->searchable(),
                TextColumn::make('phones')
                    ->label('Телефон')
                    ->getStateUsing(fn (Lead $record): string => $record->phones),
                TextColumn::make('emails')
                    ->label('Электронная почта')
                    ->getStateUsing(fn (Lead $record): string => $record->emails),
                TextColumn::make('applications')
                    ->label('Кол-во заявок СПТ')
                    ->getStateUsing(fn (Lead $record): string => $record->applications()->count() ?? 0),
                TextColumn::make('applications_by_country')
                    ->label('Из них по странам')
                    ->getStateUsing(fn (Lead $record): string => $record->applications
                        ->groupBy('country.short_name')
                        ->map(fn ($group) => $group->count())
                        ->sortByDesc(fn ($count) => $count)
                        ->map(fn ($count, $country) => "$country - $count")
                        ->implode('; ')
                    ),
                TextColumn::make('applications_by_type')
                    ->label('Из них по типам')
                    ->getStateUsing(fn (Lead $record): string => $record->applications
                        ->groupBy('type.short_name')
                        ->map(fn ($group) => $group->count())
                        ->sortByDesc(fn ($count) => $count)
                        ->map(fn ($count, $type) => "$type - $count")
                        ->implode('; ')
                    ),
                TextColumn::make('status')
                    ->badge()
                    ->label('статус')
                    ->sortable(),
            ])
            ->poll('60s')//задан интервал обновления списка заказов
            ->actions([
                Action::make('open')
                    ->label('')
                    ->tooltip('просмотр')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Model $record): string => LeadResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
            ])
            ->headerActions([
                ExportAction::make()->label('Экспорт'),
            ]);

    }
}
