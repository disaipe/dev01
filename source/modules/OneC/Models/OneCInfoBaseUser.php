<?php

namespace App\Modules\OneC\Models;

use App\Core\Reference\ReferenceModel;
use App\Modules\ActiveDirectory\Models\ADUserEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int one_c_info_base_id
 * @property string username
 * @property string login
 * @property string domain
 * @property string company_code
 * @property ADUserEntry ad_user
 */
class OneCInfoBaseUser extends ReferenceModel
{
    protected $fillable = [
        'one_c_info_base_id',
        'username',
        'login',
        'domain',
    ];

    protected $appends = [
        'company_code',
    ];

    protected $hidden = [
        'ad_user',
    ];

    public function ad_user(): BelongsTo
    {
        return $this->belongsTo(ADUserEntry::class, 'login', 'username');
    }

    public function companyCode(): Attribute
    {
        return Attribute::get(fn () => $this->ad_user?->company?->code);
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas(
            'ad_user',
            fn (Builder $user) => $user->active()->company($code)
        );
    }
}
