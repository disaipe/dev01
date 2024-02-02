<?php

namespace App\Services;

use App\Facades\Auth;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Storage;
use Lab404\Impersonate\Services\ImpersonateManager;

class VueAppService
{
    public static function render($view, $share = [], $bladeShares = []): View
    {
        $data = (new self())->getPropsData($share);

        return view($view, ['vueData' => json_encode($data), ...$bladeShares]);
    }

    private function getPropsData($share = []): array
    {
        /** @var DashboardMenuService $menu */
        $menu = app('menu');

        $referenceService = new ReferenceService();

        $share = array_merge_recursive([
            'menu' => $menu->getMenu(),
            'user' => $this->getUserProps(),
            'routes' => $referenceService->getVueRoutes(),
            'models' => $referenceService->getModels(),
        ], $share);

        $key = Encrypter::generateKey('aes-128-cbc');
        $encrypter = new Encrypter($key);

        return [
            'k' => base64_encode($key),
            'v' => $encrypter->encrypt(json_encode($share, JSON_OBJECT_AS_ARRAY), false),
        ];
    }

    private function getUserProps(): ?array
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $avatar = Storage::disk('public')->exists("avatars/{$user->id}")
            ? "/storage/avatars/{$user->id}"
            : null;

        return [
            ...$user?->only('name'),
            'avatar' => $avatar,
            'isClient' => $user->isClient(),
            'isImpersonating' => $this->getIsImpersonating(),
            'hasAdminAccess' => $user->canAccessPanel(Filament::getCurrentPanel()),
            'companies' => $user->companies()->pluck('name', 'id'),
        ];
    }

    /**
     * Returns false if impersonating is off or url to leave this mode
     *
     * @return string|false
     */
    private function getIsImpersonating(): string|false
    {
        /** @var ImpersonateManager $impersonate */
        $impersonate = app('impersonate');

        return $impersonate->isImpersonating()
            ? route('impersonate.leave')
            : false;
    }
}
