<?php

namespace App\Listeners;

use App\Models\Domain;
use App\Services\LdapService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Storage;
use LdapRecord\Container;
use LdapRecord\Models\Entry;

class AuthUserLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {
        $domainCode = $event->user->domain;

        if ($domainCode) {
            $id = $event->user->id;
            $email = $event->user->email;
            $domain = Domain::query()->where('code', '=', $domainCode)->first();

            if ($domain) {
                LdapService::addDomainConnection($domain);
                Container::setDefault($domain->code);

                $entry = Entry::query()->select('photo')->where('mail', '=', $email)->first();

                $photo = $entry->getFirstAttribute('photo');

                if ($photo) {
                    Storage::disk('public')->put("avatars/{$id}", $photo);
                }
            }
        }
    }
}
