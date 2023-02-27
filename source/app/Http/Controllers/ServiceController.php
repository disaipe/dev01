<?php

namespace App\Http\Controllers;

use App\Core\Reference\ReferenceController;
use App\Core\Reference\ReferenceModel;
use App\Models\Service;

class ServiceController extends ReferenceController
{
    protected string|ReferenceModel $model = Service::class;
}
