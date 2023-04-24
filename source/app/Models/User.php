<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int id
 * @property string name
 * @property string email
 * @property string domain
 */
class User extends Authenticatable implements FilamentUser
{
    use AuthenticatesWithLdap, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'domain',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Determine the user can access administration panel
     */
    public function canAccessFilament(): bool
    {
        return $this->canAny(['super admin', 'admin']);
    }
}
