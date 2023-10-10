<?php

namespace App\Support\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SqlConnectionSettingsForm
{
    public static function make(string $prefix = '', \Closure $afterUpdate = null): array
    {
        return [
            Select::make("{$prefix}db_driver")
                ->label(__('support.sql connection settings.driver'))
                ->default('sqlsrv')
                ->required()
                ->reactive()
                ->options([
                    'sqlsrv' => 'SQL Server',
                ])
                ->columnSpanFull(),

            TextInput::make("{$prefix}db_host")
                ->label(__('support.sql connection settings.host'))
                ->reactive()
                ->required()
                ->afterStateUpdated($afterUpdate),

            TextInput::make("{$prefix}db_port")
                ->label(__('support.sql connection settings.port'))
                ->numeric()
                ->reactive()
                ->required(),

            TextInput::make("{$prefix}db_username")
                ->label(__('support.sql connection settings.login'))
                ->reactive()
                ->required(),

            TextInput::make("{$prefix}db_password")
                ->label(__('support.sql connection settings.password'))
                ->password()
                ->reactive()
                ->required(),

            TextInput::make("{$prefix}db_name")
                ->label(__('support.sql connection settings.database'))
                ->reactive()
                ->required(),

            Select::make("{$prefix}sslmode")
                ->label('SSL')
                ->options([
                    'disable' => 'Disable',
                    'allow' => 'Allow',
                    'prefer' => 'Prefer',
                    'require' => 'Require',
                ])
                ->reactive(),

            Textarea::make("{$prefix}driver_options")
                ->label(__('support.sql connection settings.driver options'))
                ->helperText(__('support.sql connection settings.driver options help'))
                ->rows(2)
                ->reactive()
                ->columnSpanFull(),
        ];
    }
}
