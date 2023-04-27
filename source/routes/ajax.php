<?php

use Illuminate\Support\Facades\Route;

Route::reference(\App\Reference\CompanyReference::class);
Route::reference(\App\Reference\ServiceReference::class);
Route::reference(\App\Reference\ServiceProviderReference::class);
Route::reference(\App\Reference\ContractReference::class);
Route::reference(\App\Reference\ReportTemplateReference::class);
Route::reference(\App\Reference\PriceListReference::class);
Route::reference(\App\Reference\PriceListValueReference::class);

Route::references();

Route::post('batch', \App\Http\Controllers\BatchController::class);

Route::post('indicator', \App\Http\Controllers\IndicatorController::class);

Route::prefix('price_list/{priceList}')
    ->controller(\App\Http\Controllers\PriceListController::class)
    ->group(function () {
        Route::get('', 'list');
        Route::post('', 'update');
    });

Route::match(['GET', 'POST'], 'report', \App\Http\Controllers\ReportController::class);
