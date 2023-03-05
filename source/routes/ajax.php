<?php

use Illuminate\Support\Facades\Route;

Route::reference(\App\Reference\CompanyReference::class);
Route::reference(\App\Reference\ServiceReference::class);
Route::reference(\App\Reference\ServiceProviderReference::class);
Route::reference(\App\Reference\ReportTemplateReference::class);

Route::references();

Route::post('indicator', \App\Http\Controllers\IndicatorController::class);

Route::match(['GET', 'POST'], 'report', \App\Http\Controllers\ReportController::class);
