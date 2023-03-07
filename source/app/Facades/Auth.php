<?php

namespace App\Facades;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth as Facade;

class Auth extends Facade
{
    const LOGIN_FIELD = 'email';

    const LDAP_LOGIN_FIELD = 'mail';

    /**
     * Returns array of available guards
     *
     * @return string[]
     */
    public static function guards(): array
    {
        $guards = [
            config('auth.defaults.guard'),
        ];

        if (config('auth.ldap.enabled', true)) {
            $guards[] = 'ldap';
        }

        return $guards;
    }

    /**
     * Attempt to authenticate a user using the given credentials
     * in all guards
     */
    public static function attempt(array $credentials = [], bool $remember = false): bool
    {
        $found = false;

        foreach (Auth::guards() as $guardName) {
            /** @var StatefulGuard $guard */
            $guard = Auth::guard($guardName);

            // TODO: find normal way to do this
            if ($guardName === 'ldap') {
                $userExist = User::query()
                    ->where('email', '=', Arr::get($credentials, self::LOGIN_FIELD))
                    ->whereNull('guid')
                    ->exists();

                // skip ldap sync/attempt if usual user already exist
                if ($userExist) {
                    continue;
                }

                $credentials[self::LDAP_LOGIN_FIELD] = Arr::get($credentials, self::LOGIN_FIELD);
                unset($credentials[self::LOGIN_FIELD]);
            }

            if ($guard->attempt($credentials, $remember)) {
                // $found = true;
                return true;
            }
        }

        return $found;
    }

    /**
     * Determine if the current user is authenticated.
     */
    public static function check(): bool
    {
        foreach (Auth::guards() as $guardName) {
            $guard = Auth::guard($guardName);
            try {
                if ($guard->check()) {
                    return true;
                }
            } catch (Exception) {
            }
        }

        return false;
    }
}
