<?php

namespace App\Filament\Resources\DomainResource\Pages;

use App\Filament\Resources\DomainResource;
use App\Models\Domain;
use App\Services\LdapService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use LdapRecord\Container;

class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return trans_choice('admin.domain', 1);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label(__('admin.$domain.test connection'))
                ->action('testConnection'),

            DeleteAction::make(),
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
