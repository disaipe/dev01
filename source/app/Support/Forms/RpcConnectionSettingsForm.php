<?php

namespace App\Support\Forms;

use App\Filament\Components\FormButton;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class RpcConnectionSettingsForm
{
    public static function make(string $prefix = '', string $endpoint = ''): array
    {
        return [
            TextInput::make('base_url')
                ->label(__('admin.rpc service.base url'))
                ->helperText(new HtmlString(__('admin.rpc service.base url help')))
                ->required(),

            TextInput::make('secret')
                ->label(__('admin.rpc service.secret'))
                ->helperText(__('admin.rpc service.secret help')),

            FormButton::make('testConnection')
                ->label(__('admin.rpc service.test service.title'))
                ->action(fn ($state) => self::testConnection($state, $endpoint)),
        ];
    }

    protected static function testConnection(array $state, string $endpoint): void
    {
        $appUrl = Arr::get($state, 'base_url');
        $secret = Arr::get($state, 'secret');

        $notifyType = 'danger';

        try {
            $resp = Http::withHeaders(['X-SECRET' => $secret])
                ->asJson()
                ->post("$appUrl/$endpoint");

            if ($resp->status() == 400) {
                $notifyType = 'success';
                $notifyMessage = __('admin.rpc service.test service.success');
            } elseif ($resp->unauthorized()) {
                $notifyMessage = __('admin.rpc service.test service.wrong secret');
            } else {
                $notifyMessage = __('admin.rpc service.test service.request failed').$resp->reason();
            }
        } catch (\Exception $e) {
            $notifyMessage = __('admin.error').': '.$e->getMessage();
        }

        Notification::make()->$notifyType()->title($notifyMessage)->send();
    }
}
