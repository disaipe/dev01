<?php

namespace App\Core\Module;

use App\Core\Enums\JobProtocolState;
use App\Models\JobProtocol;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class ModuleScheduledJob implements ShouldQueue //, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, Queueable, InteractsWithQueue;

    protected Module $module;

    protected string $jobId;

    public ?string $description = null;

    abstract public function work(): ?array;

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 3600;

    public function __construct()
    {
        $this->jobId = Str::uuid();
    }

    final public function handle()
    {
        $uuid = $this->job?->uuid();

        if ($uuid) {
            JobProtocol::query()
                ->where('uuid', '=', $uuid)
                ->update([
                    'state' => JobProtocolState::Work,
                    'started_at' => Carbon::now(),
                ]);
        }

        $failed = false;

        try {
            $result = $this->work();
        } catch (\Exception $e) {
            $failed = true;
            $result = [
                'error' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ];

            Log::error($e);
        }

        if ($uuid) {
            JobProtocol::query()
                ->where('uuid', '=', $uuid)
                ->update([
                    'result' => $result,
                    'state' => $failed ? JobProtocolState::Failed : JobProtocolState::Ready,
                    'ended_at' => Carbon::now(),
                ]);
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return get_class($this);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getModule(): ?Module
    {
        $modules = app('modules');

        return $modules->getByNamespace(get_class($this));
    }

    public function getModuleConfig(): array
    {
        $module = $this->getModule();

        if ($module) {
            return $module->getConfig();
        }

        return [];
    }
}
