<?php

use Illuminate\Support\Facades\Route;

Route::reference(\App\Reference\CompanyReference::class);
Route::reference(\App\Reference\ServiceReference::class);
Route::reference(\App\Reference\ServiceProviderReference::class);

Route::references();
