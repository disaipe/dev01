<?php

namespace App\Services;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceManager;
use App\Facades\Auth;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class VueAppService
{
    public static function render($view, $share = []): View
    {
        $data = (new self())->getPropsData($share);

        return view($view, ['vueData' => json_encode($data)]);
    }

    private function getPropsData($share = []): array
    {
        /** @var User $user */
        $user = Auth::user();

        $share = array_merge_recursive([
            'user' => $user?->only('name'),
            'routes' => $this->getReferenceRoutes(),
            'models' => $this->getReferenceModels(),
        ], $share);

        $key = Encrypter::generateKey('aes-128-cbc');
        $encrypter = new Encrypter($key);

        return [
            'k' => base64_encode($key),
            'v' => $encrypter->encrypt(json_encode($share), false),
        ];
    }

    private function getReferenceRoutes(): array
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        return Arr::map($references->getReferences(), function (ReferenceEntry $entry) {
            return [
                'name' => $entry->getName(),
                'path' => $entry->getPrefix(),
                'meta' => [
                    'icon' => $entry->getIcon(),
                    'view' => $entry->getView(),
                    'title' => $entry->getPluralLabel(),
                    'permissions' => [
                        'create' => $entry->canCreate(),
                        'update' => $entry->canUpdate(),
                        'delete' => $entry->canDelete(),
                    ],
                ],
            ];
        });
    }

    private function getReferenceModels(): array
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        return collect($references->getReferences())
            ->filter(fn (ReferenceEntry $ref) => $ref->hasPiniaBindings())
            ->map(function (ReferenceEntry $entry) {
                return [
                    'name' => $entry->getName(),
                    'entity' => Str::kebab(Str::plural($entry->getName())),
                    'fields' => $entry->getPiniaFields(),
                ];
            })
            ->toArray();
    }
}
