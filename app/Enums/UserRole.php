<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Member = 'member';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => __('Super Admin'),
            self::Admin => __('Admin'),
            self::Member => __('Member'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'warning',
            self::Member => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SuperAdmin => 'pi pi-shield',
            self::Admin => 'pi pi-shield',
            self::Member => 'pi pi-user',
        };
    }
}
