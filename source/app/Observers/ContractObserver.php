<?php

namespace App\Observers;

use App\Models\Contract;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        if ($contract->is_actual) {
            $this->setActual($contract);
        }
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        if ($contract->is_actual) {
            $this->setActual($contract);
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }

    protected function setActual(Contract $contract): void
    {
        Contract::query()
            ->whereNot($contract->getKeyName(), '=', $contract->getKey())
            ->where('company_id', '=', $contract->company_id)
            ->where('service_provider_id', '=', $contract->service_provider_id)
            ->update([
                'is_actual' => false,
            ]);
    }
}
