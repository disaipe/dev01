<?php

use App\Http\Controllers;
use App\Http\Middleware\VerifyCsrfToken;
use App\Reference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::reference(Reference\CompanyReference::class);
Route::reference(Reference\ServiceReference::class);
Route::reference(Reference\ServiceProviderReference::class);
Route::reference(Reference\ContractReference::class);
Route::reference(Reference\ReportTemplateReference::class);
Route::reference(Reference\PriceListReference::class);
Route::reference(Reference\PriceListValueReference::class);

Route::references();

Route::post('batch', Controllers\BatchController::class);

Route::post('indicator', Controllers\IndicatorController::class);

Route::prefix('price_list/{priceList}')
    ->controller(Controllers\PriceListController::class)
    ->group(function () {
        Route::get('', 'list');
        Route::post('', 'update');
        Route::post('/copy', 'copy');
    });

Route::prefix('report')
    ->controller(Controllers\ReportController::class)
    ->group(function () {
        Route::match(['GET', 'POST'], '', 'makeReport');
    });

Route::prefix('office')
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->controller(Controllers\OnlyOfficeController::class)
    ->group(function () {
        Route::get('download', 'getFileContent');
        Route::post('info', 'getFileInfo');

        Route::match(['GET', 'POST'], 'cb', 'callbackAction');
    });