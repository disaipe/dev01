<?php

namespace App\Modules\ActiveDirectory\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;

class ADComputerEntry extends ReferenceModel
{
    use CompanyScope;

    protected $table = 'ad_computer_entries';

    protected ?string $companyCodeColumn = 'company_prefix';

    protected $fillable = [
        'name',
        'ou_path',
        'operating_system',
        'operating_system_version',
        'dns_name',
        'created_at',
        'updated_at',
        'synced_at',
        'deleted_at',
    ];
}
