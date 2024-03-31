<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';
    protected static ?string $navigationGroup = 'Система';
    protected static ?string $navigationLabel = 'Видимость столбцов';
    protected static ?string $modelLabel = 'настройка';
    protected static ?string $pluralModelLabel = 'настройки';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('role_name')
                    ->label('Наименование')
                    ->unique(ignoreRecord: true)
                    ->minLength(2)
                    ->maxLength(255),
                Tabs::make('Таблицы')
                    ->tabs([
                        Tabs\Tab::make('Сертификаты')
                            ->schema([
                                CheckboxList::make('columns_visibility')
                                    ->label('Выберите столбцы для видимости')
                                    ->options([
                                        'certificate_number' => 'номер заявки',
                                        'certificate_date' => 'дата',
                                        'certificate_type_short_name' => 'тип сертификата',
                                        'certificate_sign_name' => 'признак',
                                        'certificate_chamber_short_name' => 'палата',
                                        'certificate_payer_short_name' => 'плательщик',
                                        'certificate_sender_short_name' => 'откуда',
                                        'certificate_company_short_name' => 'куда',
                                        'certificate_company_country_short_name' => 'страна получателя',
                                        'certificate_scan_path' => 'скан',
                                        'certificate_expert_full_name' => 'эксперт',
                                        'certificate_invoice_issued' => 'счет выставлен',
                                        'certificate_paid' => 'счет оплачен',
                                        'certificate_delivery_id' => 'статус доставки',
                                        'certificate_deleted_at' => 'удалена запись',
                                        'certificate_created_at' => 'создана запись',
                                        'certificate_updated_at' => 'отредактирована запись',
                                    ])
                                    ->bulkToggleable()
                            ]),
                        Tabs\Tab::make('Доставки')
                            ->schema([
                                CheckboxList::make('columns_visibility')
                                    ->label('Выберите столбцы для видимости')
                                    ->options([
                                        'delivery_number' => 'номер доставки',
                                        'delivery_accepted_at' => 'принято в доставку',
                                        'delivery_organization_short_name' => 'получатель',
                                        'delivery_deliveryman_full_name' => 'курьер',
                                        'delivery_cost' => 'стоимость',
                                        'delivery_certificates_count' => 'количество передаваемых сертификатов',
                                        'delivery_is_pickup' => 'самовывоз',
                                        'delivery_delivered_at' => 'дата и время доставки',
                                        'delivery_deleted_at' => 'удалена запись',
                                        'delivery_created_at' => 'создана запись',
                                        'delivery_updated_at' => 'отредактирована запись',
                                    ])
                                    ->bulkToggleable()
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role_name')
                ->label('Роль'),
                TextColumn::make('columns_visibility')
                    ->label('Настройки')
                    ->badge()
                    ->getStateUsing(fn (Setting $record): string => $record->columns_visibility == null ? '' : 'Присутствуют')
                    ->colors([
                        'success' => 'Присутствуют',
                    ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}
