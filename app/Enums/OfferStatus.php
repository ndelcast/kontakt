<?php

namespace App\Enums;

enum OfferStatus: string
{
    case New = 'new';
    case Seen = 'seen';
    case Hot = 'hot';
    case Ignored = 'ignored';
    case Converted = 'converted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => __('New'),
            self::Seen => __('Seen'),
            self::Hot => __('Hot'),
            self::Ignored => __('Ignored'),
            self::Converted => __('Converted'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'info',
            self::Seen => 'gray',
            self::Hot => 'danger',
            self::Ignored => 'gray',
            self::Converted => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'pi pi-sparkles',
            self::Seen => 'pi pi-eye',
            self::Hot => 'pi pi-bolt',
            self::Ignored => 'pi pi-times',
            self::Converted => 'pi pi-check',
        };
    }
}
