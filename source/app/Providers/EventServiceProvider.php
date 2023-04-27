<?php

namespace App\Providers;

use App\Core\Enums\JobProtocolState;
use App\Core\Module\Module;
use App\Core\Module\ModuleScheduledJob;
use App\Listeners\AuthUserLoginListener;
use App\Models\Contract;
use App\Models\JobProtocol;
use App\Models\PriceList;
use App\Observers\ContractObserver;
use App\Observers\PriceListObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            AuthUserLoginListener::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Contract::observe(ContractObserver::class);
        PriceList::observe(PriceListObserver::class);

        $this->observeQueue();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Write queued job protocol after it finishes
     */
    protected function observeQueue(): void
    {
        Queue::before(function (JobProcessing $event) {
            $uuid = $event->job->uuid();
            $name = $event->job->resolveName();
            $payload = $event->job->payload();
            $description = null;
            $moduleKey = null;

            $jobCommand = Arr::get($payload, 'data.command');
            if ($jobCommand) {
                /** @var ModuleScheduledJob $job */
                $job = unserialize($jobCommand);

                if (method_exists($job, 'getModule')) {
                    /** @var Module $module */
                    $module = $job->getModule();
                    $moduleKey = $module->getKey();
                }

                if (method_exists($job, 'getDescription')) {
                    $description = @$job->getDescription();
                }
            }

            JobProtocol::query()->create([
                'uuid' => $uuid,
                'name' => $name,
                'description' => $description,
                'module' => $moduleKey,
                'state' => JobProtocolState::Create,
            ]);
        });
    }
}
