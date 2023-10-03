<?php

namespace App\Modules\Directum\Models;

use App\Modules\Directum\DirectumServiceProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int XRecID
 * @property int UserID
 * @property string UserKod
 * @property string UserName
 * @property string UserType
 * @property string UserStatus
 * @property string NeedEncode
 * @property string XRecStat
 * @property string IsMainServer
 * @property string UserCategory
 * @property string LastUpdateAR
 * @property int ParentGroup
 * @property string CanChangeMembers
 * @property string UserLogin
 * @property string IsPublic
 * @property string AdditionalInfo
 * @property string IsPasswordPolicy
 * @property string Domain
 */
class MBUser extends Model
{
    protected $connection = DirectumServiceProvider::CONNECTION_NAME;

    protected $table = 'MBUser';

    protected $primaryKey = 'XRecID';
}
