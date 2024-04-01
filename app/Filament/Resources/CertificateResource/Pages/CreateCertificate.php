<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\Type;
use Carbon\Carbon;
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

        // подставляем стоимость в зависимости от типа сертфиката
        $typeId = $data['type_id'];
        $priceArr = Type::query()
            ->select('price')
            ->whereKey($typeId)
            ->pluck('price')
            ->toArray();
        $price = array_values($priceArr)[0];
        if ($user->hasRole(['Эксперт'])) {
            $data['expert_id'] = $user->expert->id;
        }
        if ($data['scan_path'] !==null) {
            $data['cost'] = $price;
        }
        return $data;
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
