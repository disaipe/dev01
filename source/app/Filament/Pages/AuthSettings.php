<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;

class AuthSettings extends Page implements Forms\Contracts\HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth-settings';

    public ?int $timeout;

    protected static ?string $navigationGroup = 'Test';
    protected static ?int $navigationSort = 1000;

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
