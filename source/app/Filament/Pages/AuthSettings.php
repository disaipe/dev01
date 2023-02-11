<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;

class AuthSettings extends Page implements Forms\Contracts\HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth-settings';

    public ?string $host;

    public ?int $port;

    public ?string $username;

    public ?string $password;

    public ?string $baseDN;

    public ?int $timeout;

    public function submit()
    {
        $this->form->getState();
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('LDAP')
            ->schema([
                Forms\Components\TextInput::make('host')
                    ->label('Host')
                    ->required(),
            ]),
        ];
    }
}
