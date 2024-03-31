<?php

namespace App\Filament\Resources\DeliveryResource\Actions;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class SetDeliveredAtDate extends Action
{
    protected function setUp(): void
    {
        $this->label('Доставлено')
            ->tooltip('Поставить отметку о доставке')
            ->icon('heroicon-o-document-check')
            ->action(function ($record) {
                if ($record->certificates()->exists()) { // Проверяем на наличие связанных записей
                    if ($record->delivered_at === null) { // Проверяем, что поле даты равно null
                        $record->update([
                            'delivered_at' => Carbon::now(), //
                        ]);
                        Notification::make()
                            ->title('Успех')
                            ->body('Текущая дата установлена.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Внимание')
                            ->body('Дата уже установлена.')
                            ->danger()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Ошибка')
                        ->body('Отсутствуют доставляемые сертификаты.')
                        ->danger()
                        ->send();
                }
            });
    }
}
