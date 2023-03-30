<?php

namespace App\Models;

use App\Services\CustomReferenceTableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string display_name
 * @property string label
 * @property string plural_label
 * @property bool company_context
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
        'schema',
        'enabled'
    ];

    protected $casts = [
        'schema' => 'json'
    ];

    public function getReferenceModel(): Reference
    {
        $tableName = CustomReferenceTableService::getTableName($this->name);

        $instance = new class($tableName) extends Reference
        {
            public static ?string $referenceTable;
            public static bool $companyContext;

            public function __construct($tableName = null)
            {
                parent::__construct([]);

                $this->setTable($tableName ?? static::$referenceTable);

                static::$referenceTable = $this->getTable();
            }

            public function scopeCompany(Builder $query, string $code): Builder
            {
                if (static::$companyContext) {
                    /** @var Company $company */
                    $company = Company::query()->firstWhere('code', '=', $code);

                    if ($company) {
                        return $query->where('company_id', '=', $company->getKey());
                    }
                }

                return $query;
            }
        };

        $instance::$companyContext = $this->company_context;

        return $instance;
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', 1);
    }
}
