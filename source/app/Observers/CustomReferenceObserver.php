<?php

namespace App\Observers;

use App\Models\CustomReference;
use App\Services\CustomReferenceTableService;
use Illuminate\Support\Facades\Schema;

class CustomReferenceObserver
{
    /**
     * Handle the CustomReference "created" event.
     */
    public function created(CustomReference $customReference): void
    {
        //
    }

    /**
     * Handle the CustomReference "updated" event.
     */
    public function updated(CustomReference $customReference): void
    {
        //
    }

    /**
     * Handle the CustomReference "deleted" event.
     */
    public function deleted(CustomReference $customReference): void
    {
        $tableName = CustomReferenceTableService::getTableName($customReference->name);
        Schema::dropIfExists($tableName);
    }

    /**
     * Handle the CustomReference "restored" event.
     */
    public function restored(CustomReference $customReference): void
    {
        //
    }

    /**
     * Handle the CustomReference "force deleted" event.
     */
    public function forceDeleted(CustomReference $customReference): void
    {
        //
    }
}
