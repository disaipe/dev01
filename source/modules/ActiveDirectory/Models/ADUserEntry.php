<?php

namespace App\Modules\ActiveDirectory\Models;

use App\Core\Reference\ReferenceModel;
use App\Core\Traits\CompanyScope;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string company_prefix
 * @property string company_name
 * @property string username
 * @property string name
 * @property string department
 * @property string post
 * @property string email
 * @property string ou_path
 * @property array groups
 * @property Carbon last_logon
 * @property string logon_count
 * @property int state
 * @property bool sip_enabled
 * @property string mailbox_guid
 * @property bool blocked
 * @property Company company
 */
class ADUserEntry extends ReferenceModel
{
    use CompanyScope;

    protected $table = 'ad_user_entries';

    protected ?string $companyCodeColumn = 'company_prefix';

    protected $fillable = [
        'company_prefix',
        'company_name',
        'username',
        'name',
        'department',
        'post',
        'email',
        'ou_path',
        'groups',
        'last_logon',
        'logon_count',
        'state',
        'sip_enabled',
        'mailbox_guid',
        'blocked',
    ];

    protected $casts = [
        'groups' => 'array',
        'last_logon' => 'datetime:Y-m-d H:i:s',
        'sip_enabled' => 'boolean',
        'blocked' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, $this->getCompanyColumn(), 'code');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('blocked', 0);
    }
}
