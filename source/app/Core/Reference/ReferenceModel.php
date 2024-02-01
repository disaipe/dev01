<?php

namespace App\Core\Reference;

use App\Core\Traits\CanListRelations;
use App\Core\Traits\Filterable;
use App\Core\Traits\Protocolable;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class ReferenceModel extends Model
{
    use CanListRelations, Filterable, Protocolable, SoftDeletes;

    protected $hidden = [
        'deleted_at',
    ];

    protected array $sortable = [
        // Specify additional column names here to be able to sort by them.
        // Сan be used in complex models with joins or complex queries
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope());
    }

    public function isSortable(string $field): bool
    {
        if (! $field) {
            return false;
        }

        if (in_array($field, $this->sortable)) {
            return true;
        }

        if (Schema::hasColumn($this->getTable(), $field)) {
            return true;
        }

        return false;
    }
}
