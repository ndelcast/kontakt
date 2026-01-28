<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Select::make('locale')
                    ->label(__('Language'))
                    ->options([
                        'en' => 'English',
                        'fr' => 'Français',
                        'es' => 'Español',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }
}
