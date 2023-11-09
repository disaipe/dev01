<?php

namespace App\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class RpcClient
{
    protected string $base = '/';

    protected string $path = '';

    protected string $secret = '';

    protected string $auth = '';

    public static function make(): static
    {
        return new static();
    }

    public function setBase(string $base): static
    {
        $this->base = $base;

        return $this;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    public function setAuth(string $auth): static
    {
        $this->auth = $auth;

        return $this;
    }

    public function post(array $data): Response
    {
        return Http::baseUrl($this->base)
            ->withHeaders($this->getHeaders())
            ->post($this->path, $data);
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-SECRET' => $this->secret,
            'X-APP-AUTH' => Crypt::encryptString($this->auth.'|'.config('app.url')),
        ];
    }
}
