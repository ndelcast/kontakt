<?php

namespace App\Enums;

enum TaskType: string
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

    public function getColor(): string|array|null
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
            self::Call => 'pi pi-phone',
            self::Email => 'pi pi-envelope',
            self::Meeting => 'pi pi-calendar',
            self::FollowUp => 'pi pi-replay',
            self::Note => 'pi pi-file',
            self::Other => 'pi pi-ellipsis-h',
        };
    }
}
