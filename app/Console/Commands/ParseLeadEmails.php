<?php

namespace App\Console\Commands;

use App\Enums\LeadStatus;
use App\Models\Application;
use App\Models\Country;
use App\Models\Lead;
use App\Models\Type;
use App\Services\MailboxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ParseLeadEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-lead-emails {folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг контактных данных потенциальных заказчиков из содержимого текста письма';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Получение аргумента type
        $folder = $this->argument('folder');
        $mailboxService = new MailboxService($folder);
        Log::channel('mailbox_import')->info('Импорт с почты запущен на папку: ' . $folder);

        $results = $mailboxService->getMessageBody();
        foreach ($results as $result) {
            $this->parseEmail($result);
        }

        $this->info('Email parsing completed.');
    }

    public static function getLeadId(string $leadName)
    {
        //приводим к нижнему регистру значение наименования заявителя
        $lowercaseLeadName = mb_strtolower($leadName);
        //найти заявителя по наименованию или алиасу
        $lead = Lead::query()
            ->whereRaw("LOWER(name) = ?", [$lowercaseLeadName])
            ->orWhereJsonContains('aliases', $lowercaseLeadName)
            ->first();
        // если заявитель не найден, то создаем новый
        $lead = $lead ?? Lead::create([
            'name' => $leadName,
            'status' => LeadStatus::New,
        ]);
        return $lead->id;
    }


    protected function parseEmail($content)
    {
/*        // Регулярные выражения для извлечения данных
        $numberPattern = '/СПТ № ([\w-]+)/';
        $typePattern = '/Тип СПТ: (.+)/';
        $countryPattern = '/Страна экспорта: (.+)/';
        $applicantPattern = '/Заявитель: (.+)/';
        $phonePattern = '/Телефон заявителя: (.+)/';
        $emailPattern = '/Почта заявителя: (.+)/';
        $innPattern = '/ИНН экспортера: (.+)/';
        $exporterNamePattern = '/Название экспортера: (.+)/';*/


        // Удаление символов новой строки
        $content = str_replace("\r\n", "", $content);

        // Регулярные выражения для извлечения данных
        $numberPattern = '/СПТ\s*№\s*([\w-]+)/';
        $typePattern = '/Тип СПТ: (.+?)(?=Страна экспорта:|$)/';
        $countryPattern = '/Страна экспорта: (.+?)(?=Заявитель:|$)/';
        $applicantPattern = '/Заявитель: (.+?)(?=Телефон заявителя:|$)/';
        $phonePattern = '/Телефон заявителя: (.+?)(?=Почта заявителя:|$)/';
        $emailPattern = '/Почта заявителя: (.+?)(?=ИНН экспортера:|$)/';
        $innPattern = '/ИНН экспортера: (.+?)(?=Название экспортера:|$)/';
        $exporterNamePattern = '/Название экспортера: (.+?)(?=$)/';

        // Извлечение данных
        preg_match($numberPattern, $content, $numberMatches);
        preg_match($typePattern, $content, $typeMatches);
        preg_match($countryPattern, $content, $countryMatches);
        preg_match($applicantPattern, $content, $applicantMatches);
        preg_match($phonePattern, $content, $phoneMatches);
        preg_match($emailPattern, $content, $emailMatches);
        preg_match($innPattern, $content, $innMatches);
        preg_match($exporterNamePattern, $content, $exporterNameMatches);


        $number = isset($numberMatches[1]) ? $numberMatches[1] : null;
        $typeName = isset($typeMatches[1]) ? $typeMatches[1] : null;
        $countryName = isset($countryMatches[1]) ? $countryMatches[1] : null;
        $applicant = isset($applicantMatches[1]) ? $applicantMatches[1] : null;
        $phone = isset($phoneMatches[1]) ? $phoneMatches[1] : null;
        $email = isset($emailMatches[1]) ? $emailMatches[1] : null;
        $inn = isset($innMatches[1]) ? $innMatches[1] : null;
        $exporterName = isset($exporterNameMatches[1]) ? $exporterNameMatches[1] : null;
        // Получение или создание страны
        $country = null;
        try {
            $country = Country::query()
                ->whereRaw("LOWER(name) = ?", [strtolower($countryName)])
                ->orWhereRaw("LOWER(short_name) = ?", [strtolower($countryName)])
                ->orWhere('alpha2', $countryName)
                ->first();
            if (!$country) {
                $country = Country::create([
                    'name' => $countryName,
                    'short_name' => $countryName, // Присваиваем то же значение для short_name
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('mailbox_import')->info('Ошибка добавления/обновления страны: ' . $e->getMessage());
        }
        // Получение или создание типа сертификата
        $type = null;
        try {
            $type = Type::firstOrCreate(['short_name' => $typeName]);
        } catch (\Exception $e) {
            Log::channel('mailbox_import')->info('Ошибка добавления/обновления типа СПТ: ' . $e->getMessage());
        }

        // Сохранение данных в модель Lead, обновляя данные по номеру заявки, а при отсутствии - добавляет
        if ($country && $type)  {
            Application::updateOrCreate(
                ['number' => $number],
                [
                    'type_id' => $type->id,
                    'country_id' => $country->id,
                    'lead_id' => self::getLeadId($applicant),
                    'phone' => $phone,
                    'email' => $email,
                    'inn' => $inn,
                    'exporter_name' => $exporterName,
                ]
            );
        }

        // Вывод извлеченных данных
/*        $this->info("Parsed email:");
        $this->info("Number: $number");
        $this->info("Type: $type");
        $this->info("Country: $country");
        $this->info("Applicant: $applicant");
        $this->info("Phone: $phone");
        $this->info("Email: $email");
        $this->info("INN: $inn");
        $this->info("Exporter Name: $exporterName");*/
    }
}
