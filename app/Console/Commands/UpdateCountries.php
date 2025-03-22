<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdateCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-countries {file : Путь к Excel-файлу(например: storage/database/oksm.xlsx)}';

    /**
     * Обновление списка стран из Excel файла,
     * скачанного с Общероссийского классификатора стран (https://classifikators.ru/assets/downloads/oksm/oksm.xlsx).
     *
     * @var string
     */
    protected $description = 'Обновление списка стран из из Excel-файла';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("Файл не найден: {$file}");
            return;
        }

        try {
            // Загрузка Excel-файла
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();

            // Чтение данных из файла
            $rows = $sheet->rangeToArray('A1:F1000'); // Читаем только первые 1000 строк
            $header = array_shift($rows); // Убираем заголовок
            foreach ($rows as $row) {
                // Предполагаем, что структура файла: №, Код, Краткое название,	Полное название, Альфа-2, Альфа-3
                [$serialNumber, $code, $shortName, $name, $alpha2, $alpha3 ] = $row;
                // Валидация данных
                if (empty($code) || empty($shortName)) {
                    $this->warn("Пропущена строка с некорректными данными: " . implode(', ', $row));
                    continue;
                }
                // Обновляем или создаем запись в базе данных
                Country::updateOrCreate(
                    ['code' => $code],
                    [
                        'short_name' => $shortName,
                        'name' => $name,
                        'code' => $code,
                        'alpha2' => $alpha2,
                        'alpha3' => $alpha3,
                    ]
                );
            }

            $this->info('Список стран успешно обновлен из Excel-файла.');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении стран: ' . $e->getMessage());
            $this->error('Произошла ошибка: ' . $e->getMessage());
        }
    }
}
