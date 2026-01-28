<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskType: string implements HasLabel, HasColor, HasIcon
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case FollowUp = 'follow_up';
    case Note = 'note';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Call => __('Call'),
            self::Email => __('Email'),
            self::Meeting => __('Meeting'),
            self::FollowUp => __('Follow-up'),
            self::Note => __('Note'),
            self::Other => __('Other'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Call => 'success',
            self::Email => 'info',
            self::Meeting => 'warning',
            self::FollowUp => 'primary',
            self::Note => 'gray',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Call => 'heroicon-o-phone',
            self::Email => 'heroicon-o-envelope',
            self::Meeting => 'heroicon-o-calendar',
            self::FollowUp => 'heroicon-o-arrow-path',
            self::Note => 'heroicon-o-document-text',
            self::Other => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }
}
