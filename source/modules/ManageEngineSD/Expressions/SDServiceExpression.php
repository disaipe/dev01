<?php

namespace App\Modules\ManageEngineSD\Expressions;

use App\Core\Report\Expression;
use App\Modules\ManageEngineSD\Models\SDServiceDefinition;
use App\Modules\ManageEngineSD\Models\SDWorkorder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SDServiceExpression extends Expression
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

    public static function disabled(array $state): bool
    {
        $reference = Arr::get($state, 'reference');

        return $reference !== class_basename(SDWorkorder::class);
    }
}
