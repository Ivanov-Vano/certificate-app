<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateDelivery extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = DeliveryResource::class;
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Создана новая доставка';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // генерируем новый номер доставки. Формат номера: ХХХХ-YY
        $currentYear = now()->format('y');
        $maxNumber = DB::table('deliveries')
            ->where('number', 'LIKE', '%-' . $currentYear) // Убедимся, что номер соответствует текущему году
            ->max('number');


        if (!is_null($maxNumber)) {
            $numericPart = intval(substr($maxNumber, 0, 4)) + 1;
        } else {
            $numericPart = 1;
        }

        // Проверяем, не превышает ли номер максимально допустимое значение
        if ($numericPart > 9999) {
            // Начинаем нумерацию с 0001 следующего года
            $numericPart = 1;
            $year = str_pad($currentYear + 1, 2, '0', STR_PAD_LEFT); // Увеличиваем год
        } else {
            $year = $currentYear;
        }

        // Формируем новый номер с ведущими нулями
        $number = str_pad($numericPart, 4, '0', STR_PAD_LEFT) . '-' . $year;

        $data['number'] = $number;

        // генерируем дату подачи в доставку
        $data['accepted_at'] = now();

        // подставляем стоимость в зависимости от организации куда доставлять
        $orgId = $data['organization_id'];
        $priceArr = Organization::query()
            ->select('delivery_price')
            ->whereKey($orgId)
            ->pluck('delivery_price')
            ->toArray();
        $price = array_values($priceArr)[0];
        $data['cost'] = $price;

        if ($user->hasRole(['Курьер'])) {
            $data['deliveryman_id'] = $user->deliveryman->id;
        }
        return $data;
    }
    protected function getSteps(): array
    {
        return [
            Step::make('first')
                ->label('первый шаг')
                ->description('Выберите организацию для доставки')
                ->schema([
                    Select::make('organization_id')
                        ->relationship('organization', 'short_name')
                        ->required()
                        ->label('Куда доставка'),
                    Select::make('deliveryman_id')
                        ->relationship('deliveryman', 'full_name')
                        ->hidden(auth()->user()->hasRole(['Курьер']))
                        ->label('Курьер ФИО'),
                    Toggle::make('is_pickup')
                        ->label('Самовывоз'),
                ]),
        ];
    }
}
