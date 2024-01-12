<?php

namespace App\Modules\Directum\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\Directum\DirectumServiceProvider;
use App\Modules\Directum\Enums\UserStatus;
use App\Modules\Directum\Enums\UserType;
use App\Modules\Directum\Models\DirectumUser;
use App\Modules\Directum\Models\MBUser;

class DirectumSyncUsersJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        /** @var DirectumServiceProvider $provider */
        $provider = $this->getModule()->getProvider();
        $provider->setupDatabaseConnection();

        DirectumUser::query()->truncate();

        $users = MBUser::query()
            ->where('UserType', '=', UserType::User)
            ->where('UserStatus', '=', UserStatus::Active)
            ->whereNotNull('Domain')
            ->get();

        $recordsToUpsert = $users
            ->map(fn (MBUser $user) => [
                'name' => $user->UserLogin,
                'domain' => $user->Domain,
            ])
            ->toArray();

        $count = DirectumUser::query()
            ->withoutGlobalScope(DirectumUser::SELECT_SCOPE)
            ->upsert($recordsToUpsert, ['name', 'domain']);

        return [
            'result' => $count,
        ];
    }

    public function getDescription(): ?string
    {
        return __('directum::messages.job.users.title');
    }
}
