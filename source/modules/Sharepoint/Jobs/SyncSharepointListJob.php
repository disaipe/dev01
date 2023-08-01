<?php

namespace App\Modules\Sharepoint\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\Sharepoint\Models\SharepointList;
use App\Modules\Sharepoint\Services\SharepointService;
use Carbon\Carbon;
use Exception;

class SyncSharepointListJob extends ModuleScheduledJob
{
    protected int $sharepointListId;

    private ?SharepointList $sharepointList;

    public function __construct(int $sharepointListId)
    {
        parent::__construct();

        $this->sharepointListId = $sharepointListId;

        $this->sharepointList = SharepointList::query()->find($this->sharepointListId);
    }

    /**
     * @throws Exception
     */
    public function work(): ?array
    {
        if (! $this->sharepointList) {
            throw new Exception(__(
                'sharepoint::messages.jobs.sync list.errors.sharepoint list not found',
                ['id' => $this->sharepointListId]
            ));
        }

        $this->getModule()->getProvider()->setupDatabaseConnection();
        $result = SharepointService::syncList($this->sharepointList);

        $this->sharepointList->last_sync = Carbon::now();
        $this->sharepointList->saveQuietly();

        return [
            'result' => $result
                ? count($result) . ' records synced'
                : 'no records synced',
        ];
    }

    public function getDescription(): ?string
    {
        return __('sharepoint::messages.jobs.sync list.title', ['name' => $this->sharepointList->name]);
    }
}
