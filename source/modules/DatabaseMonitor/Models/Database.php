<?php

namespace App\Modules\DatabaseMonitor\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int database_server_id
 * @property string dbid
 * @property string name
 * @property int size size in bytes
 * @property string company_code
 */
class Database extends ReferenceModel
{
    use CompanyScope;

    protected $fillable = [
        'database_server_id',
        'dbid',
        'name',
        'size',
        'company_code',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, $this->getCompanyColumn(), 'code');
    }
}
