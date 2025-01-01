<?php

namespace App\Console\Commands;

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

    protected function parseEmail($content)
    {
        // Регулярные выражения для извлечения данных
        $numberPattern = '/СПТ № ([\w-]+)/';
        $typePattern = '/Тип СПТ: (.+)/';
        $countryPattern = '/Страна экспорта: (.+)/';
        $applicantPattern = '/Заявитель: (.+)/';
        $phonePattern = '/Телефон заявителя: (.+)/';
        $emailPattern = '/Почта заявителя: (.+)/';
        $innPattern = '/ИНН экспортера: (.+)/';
        $exporterNamePattern = '/Название экспортера: (.+)/';


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
        $type = isset($typeMatches[1]) ? $typeMatches[1] : null;
        $country = isset($countryMatches[1]) ? $countryMatches[1] : null;
        $applicant = isset($applicantMatches[1]) ? $applicantMatches[1] : null;
        $phone = isset($phoneMatches[1]) ? $phoneMatches[1] : null;
        $email = isset($emailMatches[1]) ? $emailMatches[1] : null;
        $inn = isset($innMatches[1]) ? $innMatches[1] : null;
        $exporterName = isset($exporterNameMatches[1]) ? $exporterNameMatches[1] : null;

        // Вывод извлеченных данных
        $this->info("Parsed email:");
        $this->info("Number: $number");
        $this->info("Type: $type");
        $this->info("Country: $country");
        $this->info("Applicant: $applicant");
        $this->info("Phone: $phone");
        $this->info("Email: $email");
        $this->info("INN: $inn");
        $this->info("Exporter Name: $exporterName");
    }
}
