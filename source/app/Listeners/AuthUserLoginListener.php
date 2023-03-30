<?php

namespace App\Listeners;

use App\Models\Domain;
use App\Models\User;
use App\Services\LdapService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LdapRecord\Container;
use LdapRecord\Models\Entry;

class AuthUserLoginListener
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $domainCode = $user->domain;

        if ($domainCode) {
            $id = $user->id;
            $email = $user->email;

            /** @var Domain $domain */
            $domain = Domain::query()->firstWhere('code', '=', $domainCode);

            if ($domain) {
                LdapService::addDomainConnection($domain);
                Container::setDefault($domain->code);

                try {
                    $entry = Entry::query()->select('photo')->where('mail', '=', $email)->first();

                    $photo = $entry->getFirstAttribute('photo');

                    if ($photo) {
                        Storage::disk('public')->put("avatars/{$id}", $photo);
                    }
                } catch (\Exception $e) {
                    Log::info("Ldap user avatar synchronization failed: {$e->getMessage()}");
                }
            }
        }
    }
}
