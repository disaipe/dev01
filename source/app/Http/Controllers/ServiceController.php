<?php

namespace App\Http\Controllers;

use App\Core\ReferenceController;
use App\Core\ReferenceModel;
use App\Models\Service;

class ServiceController extends ReferenceController
{
    protected string|ReferenceModel $model = Service::class;
}
