<?php

namespace App\Modules\ActiveDirectory;

use App\Core\Module\ModuleBaseServiceProvider;
use App\Core\Reference\ReferenceManager;
use App\Core\RegularExpressions;
use App\Models\Domain;
use App\Modules\ActiveDirectory\Commands\LdapSync;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ADBaseServiceProvider extends ModuleBaseServiceProvider
{
    protected string $namespace = 'ad';

    public function init(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', $this->namespace);

        $this->commands([
            LdapSync::class,
        ]);

        // Route::referenceFromModel(ADEntry::class);

        /** @var ReferenceManager $references */
        $references = app('references');
        $references->register(ADEntryReference::class);

        $this->setOptions([
            'view' => [
                'config' => [
                    Section::make(__('ad::messages.section_sync'))->schema([
                        Select::make('domain_id')
                            ->label(trans_choice('admin.domain', 1))
                            ->options(Domain::all()->pluck('name', 'id'))
                            ->required(),

                        TextInput::make('base_dn')
                            ->label(__('ad::messages.base_dn'))
                            ->helperText(__('ad::messages.base_dn_helper')),

                        Textarea::make('filters')
                            ->label(__('ad::messages.filter')),
                    ]),
                    Section::make(__('admin.schedule'))->schema([
                        TextInput::make('schedule')
                            ->label('Cron')
                            ->helperText(__('admin.cron_helper'))
                            ->regex(RegularExpressions::CRON),
                    ]),
                ],
            ],
        ]);
    }
}
