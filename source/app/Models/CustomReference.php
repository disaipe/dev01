<?php

namespace App\Models;

use App\Core\Enums\CustomReferenceContextType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property string name
 * @property string display_name
 * @property string label
 * @property string plural_label
 * @property bool company_context
 * @property string context_type
 * @property array schema
 */
class CustomReference extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'label',
        'plural_label',
        'company_context',
        'context_type',
        'schema',
        'enabled',
    ];

    protected $casts = [
        'schema' => 'json',
    ];

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', 1);
    }

    public function getFields(): array
    {
        $fields = Arr::get($this->schema, 'fields');

        if ($this->company_context) {
            if ($this->context_type === CustomReferenceContextType::Code->value) {
                $fields[] = [
                    'display_name' => trans_choice('reference.Company', 1),
                    'name' => 'company_code',
                    'type' => 'string',
                ];
            } else {
                $fields[] = [
                    'display_name' => trans_choice('reference.Company', 1),
                    'name' => 'company_id',
                    'type' => 'int',
                ];
            }
        }

        return $fields;
    }
}
