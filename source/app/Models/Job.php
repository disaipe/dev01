<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Job extends Model
{
    protected $casts = [
        'payload' => 'json',
        'available_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public function getName(): ?string
    {
        $fallback = class_basename(Arr::get($this->payload, 'displayName'));

        $command = Arr::get($this->payload, 'data.command');

        if (! $command) {
            return $fallback;
        }

        try {
            $job = unserialize($command);

            if (method_exists($job, 'getDescription')) {
                return @$job->getDescription();
            }
        } catch (\Exception) {
        }

        return $fallback;
    }
}
