<?php

namespace App\Modules\ManageEngineSD;

use App\Modules\ManageEngineSD\Models\SDServiceDefinition;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SDServiceExpression
{
    public static function getServices(string $search = null): array
    {
        try {
            return SDServiceDefinition::query()
                ->where('status', '=', 'ACTIVE')
                ->where('isdeleted', '=', false)
                ->when($search, fn (Builder $query) => $query->whereRaw('name like ?', '%'.$search.'%'))
                ->orderBy('name')
                ->get()
                ->pluck('name', 'serviceid')
                ->toArray();
        } catch (\Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }

        return [];
    }
}
