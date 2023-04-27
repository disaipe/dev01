<?php

namespace App\Observers;

use App\Models\PriceList;

class PriceListObserver
{
    /**
     * Handle the PriceList "created" event.
     */
    public function created(PriceList $priceList): void
    {
        if ($priceList->isDefault) {
            $this->setDefault($priceList);
        }
    }

    /**
     * Handle the PriceList "updated" event.
     */
    public function updated(PriceList $priceList): void
    {
        if ($priceList->isDefault) {
            $this->setDefault($priceList);
        }
    }

    /**
     * Handle the PriceList "deleted" event.
     */
    public function deleted(PriceList $priceList): void
    {
        //
    }

    /**
     * Handle the PriceList "restored" event.
     */
    public function restored(PriceList $priceList): void
    {
        //
    }

    /**
     * Handle the PriceList "force deleted" event.
     */
    public function forceDeleted(PriceList $priceList): void
    {
        //
    }

    protected function setDefault(PriceList $list): void
    {
        PriceList::query()
            ->whereNot($list->getKeyName(), '=', $list->getKey())
            ->where('service_provider_id', '=', $list->service_provider_id)
            ->update([
                'isDefault' => false,
            ]);
    }
}
