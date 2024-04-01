<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Delivery;
use App\Models\Deliveryman;
use App\Models\Type;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCertificate extends EditRecord
{
    protected static string $resource = CertificateResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Изменена заявка на сертификат';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {

        // подставляем стоимость в зависимости от типа сертификата
        $typeId = $data['type_id'];
        $price = Type::query()
            ->select('price', 'price_chamber')
            ->whereKey($typeId)
            ->first()
            ->toArray();

        $priceExpert = $price['price'];
        $priceChamber = $price['price_chamber'];

        if ($data['scan_path'] !==null) { //если есть скан, то подставляем стоимости
            $data['cost'] = $priceExpert;
            $data['cost_chamber'] = $priceChamber;
        }
        return $data;

    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Получаем все изменения, внесенные в запись
        $changes = $record->getChanges();

        // Проверяем, что scan_path был изменен и не пустое, а delivery_id пустое
        if (array_key_exists('scan_path', $changes) && isset($changes['scan_path'][1]) && $changes['scan_path'][1] !== null && $record->delivery_id === null) {
            // Если scan_path стал пустым после изменения, отвязываем запись от Delivery, если delivered_at пустое
            if ($changes['scan_path'][1] === '' && !empty($record->delivery_id) && $record->delivery->delivered_at === null) {
                $record->delivery()->dissociate();
                $record->save();
            } elseif ($changes['scan_path'][1] !== '' && $record->delivery_id === null) {
                // Ищем запись Delivery, где organization_id равен payer_id текущей записи Certificate и delivered_at пустое
                $delivery = Delivery::where('organization_id', $record->payer_id)
                    ->whereNull('delivered_at')
                    ->first();

                // Если такая запись существует, связываем ее с текущей записью Certificate
                if ($delivery) {
                    $record->delivery()->associate($delivery);
                    $record->save();
                } else {
                    /** Если такой записи не существует, создаем ее */
                    // генерируем новый номер доставки. Формат номера: ХХХХ-YY
                    $currentYear = now()->format('y');
                    $maxNumber = DB::table('deliveries')
                        ->where('number', 'LIKE', '%-'.$currentYear) // Убедимся, что номер соответствует текущему году
                        ->max('number');

                    if (!is_null($maxNumber) && preg_match('/^d{4}-d{2}$/', $maxNumber)) {
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

                    // Проверяем уникальность номера
                    $exists = DB::table('deliveries')->where('number', $number)->exists();
                    if ($exists) {
                        // Логика обработки ситуации, когда номер уже существует
                        throw new Exception("Генерируемый номер доставки уже присутствует в базе.");
                    }

                    //получаем идентификатор курьер, если он один в базе
                    $count = Deliveryman::query()->count();
                    $deliveryman = null;
                    if ($count === 1) {
                        $deliveryman = Deliveryman::value('id');
                    }

                    // Создаем новую запись Delivery с присвоением значения organization_id равным payer_id текущей записи Certificate
                    $delivery = new Delivery();
                    $delivery->organization_id = $record->payer_id;
                    $delivery->number = $number;
                    $delivery->accepted_at = now();
                    $delivery->deliveryman_id = $deliveryman;

                    // Получаем значение delivery_price из модели Organization через связь organization модели Delivery
                    $delivery->cost = $delivery->organization->delivery_price;

                    // Сохраняем новую запись Delivery
                    $delivery->save();
                    // Связываем созданную запись Delivery с текущей записью Certificate
                    $record->delivery()->associate($delivery);
                    $record->save();
                }
            }
        } elseif (array_key_exists('scan_path', $changes) && !isset($changes['scan_path'][1]) && $record->delivery_id !== null && $record->delivery->delivered_at === null) {
            // Если scan_path был удален, отвязываем запись от Delivery, если delivered_at пустое
            $record->delivery()->dissociate();
            $record->save();
        }
    }
}
