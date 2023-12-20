<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Models\Organization;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

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
        $year = now()->format('y');

        // генерируем новый номер доставки
        // формат номера: ХХХХ-YY
        $lastNumber = Delivery::query()->select('number')->orderBy('number')->get();
        $last = $lastNumber->pluck('number')->last();
        $last = $last ?? '0001-'.$year;   //если пусто, то первый номер: 0001-YY
        $number = (substr($last,0,(strpos($last, '-'))) + 1);
        switch (strlen($number)) {
            case 1:
                $number = '000'.$number;
                break;
            case 2:
                $number = '00'.$number;
                break;
            case 3:
                $number = '0'.$number;
                break;
            case 4:
                $number = ''.$number;
                break;
        }
        $number = $number.'-'.$year;
        $data['number'] = $number;

        // генерируем дату подачи в доставку
        $data['accepted_at'] = now();

        // подставляем стоимость в зависимости от типа сертфиката
        // (upd) не актуально, так как на форме отрабатывает подстановка
        // стоимости в зависимости от выбранной организации
/*        $orgId = $data['organization_id'];
        $priceArr = Organization::query()
            ->select('delivery_price')
            ->whereKey($orgId)
            ->pluck('delivery_price')
            ->toArray();
        $price = array_values($priceArr)[0];
        $data['cost'] = $price;*/

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
                    Select::make('deliverymen_id')
                        ->relationship('deliveryman', 'full_name')
                        ->hidden(auth()->user()->hasRole(['Курьер']))
                        ->label('Курьер ФИО'),
                    Toggle::make('is_pickup')
                        ->label('Самовывоз'),
                ]),
        ];
    }
}
