<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CertificateResource;
use Carbon\Carbon;
use Filament\Forms\Components\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PaymentToExpertByCurrentMonth extends BaseWidget
{

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Статистика по экспертам за месяц';

    public static function canView():bool
    {
        return auth()->user()->hasAnyRole(['Администратор', 'Суперпользователь', 'Руководитель', 'Эксперт']);
    }

    public function table(Table $table): Table
    {
        $month = request()->query('month', Carbon::now()->month);
        $user = auth()->user();

        return $table
            ->query(
                CertificateResource::getEloquentQuery()
                    ->when($user->role === 'Эксперт', function ($query) use ($user) {
                        return $query->whereBelongsTo($user->expert);
                    })
                    ->whereMonth('date', $month)
            )

            ->defaultPaginationPageOption(0)
            ->columns([
                TextColumn::make('cost')
                    ->summarize(Sum::make()->money('RUB'))
                    ->label('Cтоимость'),
            ])
            ->groups([
                Group::make('expert.full_name')
                    ->label('ФИО')
                    ->collapsible(),
            ])
            ->defaultGroup('expert.full_name')
            ->groupsOnly()
            ->filters([
                SelectFilter::make('month')
                    ->label('месяц')
                    ->options([
                        Carbon::now()->month => 'текущий',
                        Carbon::now()->subMonth()->month => 'предыдущий',
                    ])
                    ->default(Carbon::now()->month) // Установка фильтра по умолчанию за текущий месяц
                    ->query(function ($query, $data) {
                        $query->whereMonth('date', $data);
                    }),
            ]);
    }
}
