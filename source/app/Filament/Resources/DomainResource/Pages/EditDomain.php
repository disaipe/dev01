<?php

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use App\Models\Domain;
use App\Services\LdapService;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use LdapRecord\Container;

class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    protected function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.domain', 1);
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('test')
                ->label(__('admin.$domain.test connection'))
                ->action('testConnection'),

            Actions\DeleteAction::make(),
        ];
    }

    public function testConnection(): void
    {
        /** @var Domain $domain */
        $domain = $this->record;

        LdapService::addDomainConnection($domain);
        Container::setDefault($domain->code);

        try {
            Container::getDefaultConnection()->connect();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin.error'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('admin.$domain.connection success'))
            ->success()
            ->send();
    }
}
