<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Delivery;
use App\Models\Deliveryman;
use App\Models\Type;
use Carbon\Carbon;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Wizard;
use Filament\Tables\Actions\ReplicateAction;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;

class CreateCertificate extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = CertificateResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Создана новая заявка на сертификат';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $year = now()->format('Y');

        // генерируем новый номер заявки
        // формат номера: 20ХХ/ХХХХ
        $lastNumber = Certificate::query()->select('number')->orderBy('number')->get();
        $last = $lastNumber->pluck('number')->last();
        $last = $last ?? $year.'/0001';   //если пусто, то первый номер: 20ХХ/0001
        $number = (substr($last,(strpos($last, '/')+1)) + 1);
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
        $number = $year.'/'.$number;
        $data['number'] = $number;

        // генерируем дату заявки
        $data['date'] = now();

        // подставляем стоимость в зависимости от типа сертификата
        $typeId = $data['type_id'];
        $price = Type::query()
            ->select('price', 'price_chamber')
            ->whereKey($typeId)
            ->first()
            ->toArray();

        $priceExpert = $price['price'];
        $priceChamber = $price['price_chamber'];

        if ($user->hasRole(['Эксперт'])) {
            $data['expert_id'] = $user->expert->id;
        }
        if ($data['scan_path'] !==null) { //если есть скан, то подставляем стоимости
            $data['cost'] = $priceExpert;
            $data['cost_chamber'] = $priceChamber;
        }
        return $data;
    }
    protected function afterCreate():void
    {
        $record = $this->record;

        // Если scan_path стал пустым после изменения, отвязываем запись от Delivery, если delivered_at пустое
        if ($record->scan_path !== '' && $record->delivery_id === null) {
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
                    ->where('number', 'LIKE', '%-' . $currentYear) // Убедимся, что номер соответствует текущему году
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
                } else {
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
        }
    }

    protected function getSteps(): array
    {
        return [
            Step::make('first')
                ->label('первый шаг')
                ->description('Выберите тип, признак и палату')
                ->schema([
                    Select::make('type_id')
                        ->autofocus()
                        ->relationship('type', 'short_name')
                        ->required()
                        ->createOptionForm([
                            TextInput::make('short_name')
                                ->maxLength(100)
                                ->required()
                                ->label('Наименование'),
                            TextInput::make('name')
                                ->maxLength(255)
                                ->label('Полное наименование'),
                        ])
                        ->label('Тип сертификата'),
                    Select::make('chamber_id')
                        ->relationship('chamber', 'short_name')
                        ->required()
                        ->createOptionForm([
                            TextInput::make('short_name')
                                ->maxLength(100)
                                ->required()
                                ->label('Наименование'),
                            TextInput::make('name')
                                ->maxLength(255)
                                ->label('Полное наименование'),
                        ])
                        ->label('Торгово-промышленная палата'),
                    Section::make('Признаки')
                        ->schema([
                            Toggle::make('rec')
                                ->label('РЭЦ'),
                            Toggle::make('second_invoice')
                                ->label('2с/ф')
                        ])->columns((['md' => 1, 'lg' => 2])),
                ]),
            Step::make('second')
                ->label('второй шаг')
                ->description('Выберите плательщика, отправителя и получателя')
                ->schema([
                    Select::make('payer_id')
                        ->relationship('payer', 'short_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('short_name')
                                ->maxLength(100)
                                ->required()
                                ->label('Наименование'),
                            TextInput::make('name')
                                ->maxLength(255)
                                ->label('Полное наименование'),
                        ])
                        ->label('Организация (плательщик)'),
                    Select::make('sender_id')
                        ->relationship('sender', 'short_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('short_name')
                                ->maxLength(100)
                                ->required()
                                ->label('Наименование'),
                            TextInput::make('name')
                                ->maxLength(255)
                                ->label('Полное наименование'),
                        ])
                        ->label('Организация (откуда)'),
                    Select::make('company_id')
                        ->relationship('company', 'short_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Select::make('country_id')
                                ->relationship('country', 'short_name')
                                ->required()
                                ->label('Страна'),
                            TextInput::make('short_name')
                                ->maxLength(100)
                                ->required()
                                ->label('Наименование'),
                            TextInput::make('name')
                                ->maxLength(255)
                                ->label('Полное наименование'),
                        ])
                        ->label('Организация (куда)'),
                ]),
            Step::make('third')
                ->label('третий шаг')
                ->description('Дополнительная информация')
                ->schema([
                    Select::make('expert_id')
                        ->relationship('expert', 'full_name')
                        ->required()
                        ->hidden(auth()->user()->hasRole(['Эксперт']))
                        ->label('Эксперт ФИО'),
                    TextInput::make('extended_page')
                        ->numeric()
                        ->label('Дополнительные листы'),
                    FileUpload::make('scan_path')
                        ->directory('attachments')
                        ->openable()
                        ->acceptedFileTypes(['application/pdf'])
                        ->label('Скан сертификата'),
                ]),
        ];
    }


}
