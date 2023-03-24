<?php

namespace App\Services;

use App\Facades\Auth;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Storage;

class VueAppService
{
    public static function render($view, $share = []): View
    {
        $data = (new self())->getPropsData($share);

        return view($view, ['vueData' => json_encode($data)]);
    }

    private function getPropsData($share = []): array
    {
        $referenceService = new ReferenceService();

        $share = array_merge_recursive([
            'user' => $this->getUserProps(),
            'routes' => $referenceService->getVueRoutes(),
            'models' => $referenceService->getModels()
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
        ];
    }
}
