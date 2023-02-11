<?php

namespace App\Modules\Dummy;

use App\Core\Module\ModuleBaseServiceProvider;
use Filament\Forms\Components\TextInput;

class DummyBaseServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'dummy';

    public function init(): void
    {
        $this->setOptions([
            'view' => [
                'config' => [
                    TextInput::make('test')
                        ->label('Dummy test')
                        ->required(),
                ],
            ],
        ]);

        $this->commands([
            TestCommand::class,
        ]);
    }
}
