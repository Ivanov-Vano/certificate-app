<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'новый';

    case Worked = 'в работе';

    case Completed = 'успех';

    case Canceled = 'отказ';


    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'новый',
            self::Worked => 'в работе',
            self::Completed => 'успех',
            self::Canceled => 'отказ',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info',
            self::Worked => 'primary',
            self::Completed => 'success',
            self::Canceled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Worked => 'heroicon-m-chat-bubble-left-ellipsis',
            self::Completed => 'heroicon-m-check-badge',
            self::Canceled => 'heroicon-m-hand-thumb-down',
        };
    }
}
