<?php

namespace App\Core\Module;

use App\Facades\Config;
use App\Models\Module as Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Application module
 *
 * Allow to extend application functionality
 */
class Module
{
    /** @var string Unique key */
    private string $key;

    /** @var array Options array */
    private array $options;

    /** @var Model Module linked data model */
    private Model $model;

    /** @var ModuleBaseServiceProvider Module provider instance */
    private ModuleBaseServiceProvider $provider;

    /**
     * Create module instance
     *
     * @param  string  $key unuqie key
     * @param  array  $options options array
     */
    public function __construct(ModuleBaseServiceProvider $provider, string $key, array $options = [])
    {
        $this->provider = $provider;
        $this->key = Str::camel($key);
        $this->options = $options;

        /** @var Model $model */
        $model = Model::query()->firstOrCreate(['key' => $this->key]);
        $this->model = $model;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }

    public function getProvider(): ModuleBaseServiceProvider
    {
        return $this->provider;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSlug(): string
    {
        return Str::slug($this->key);
    }

    public function getName(): string
    {
        return Arr::get($this->options, 'name') ?? Str::ucfirst($this->key);
    }

    public function getDescription(): ?string
    {
        return Arr::get($this->options, 'description');
    }

    public function getConfigurationLayout()
    {
        return Arr::get($this->options, 'settingsView.layout');
    }

    public function getConfigKey(): string
    {
        return 'module.'.$this->getKey();
    }

    public function getConfig($path = null): mixed
    {
        $key = $this->getConfigKey();

        $data = Config::get($key, []);

        $casts = $this->getOption('casts');

        if ($casts) {
            foreach ($casts as $field => $type) {
                $value = Arr::get($data, $field);

                if ($value) {
                    $data[$field] = match ($type) {
                        'json' => json_decode($value, true),
                        'password' => Config::decryptValue($value),
                        default => $value
                    };
                }
            }
        }

        return $path
            ? Arr::get($data, $path)
            : $data;
    }

    public function setConfig($data, $prepend = false): void
    {
        $casts = $this->getOption('casts');

        if ($casts) {
            foreach ($casts as $field => $type) {
                $value = Arr::get($data, $field);

                if ($value) {
                    $data[$field] = match ($type) {
                        'json' => json_encode($value),
                        'password' => Str::contains($field, ['password'], true)
                            ? $value                         // password will be encrypted
                            : \Crypt::encryptString($value), // automatically if name contains "password"
                        default => $value
                    };
                }
            }
        }

        $configData = $prepend
            ? Arr::prependKeysWith($data, $this->getConfigKey().'.')
            : $data;

        Config::setArray($configData);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->model->enabled;
    }

    public function isHaveConfiguration(): bool
    {
        return (bool) $this->getConfigurationLayout();
    }

    public function setEnabled(bool $enabled = true): void
    {
        $this->model->update(['enabled' => $enabled]);
    }
}
