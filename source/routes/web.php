<?php

use App\Services\VueAppService;
use Illuminate\Support\Facades\Route;
use Lab404\Impersonate\Services\ImpersonateManager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('dashboard');
    return redirect('/dashboard');
});

Route::controller(\App\Http\Controllers\AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/login', 'login');
    Route::match(['get', 'post'], '/logout', 'logout')->name('logout');
});

Route::middleware('auth')->get('/dashboard/{url?}', function () {
    return VueAppService::render('dashboard');
})->where('url', '.*')->name('dashboard');

Route::get('impersonate/leave', function() {
    if(! app(ImpersonateManager::class)->isImpersonating()) {
        return redirect('/');
    }

    app(ImpersonateManager::class)->leave();

    return redirect(session()->pull('impersonate.back_to'));
})
    ->name('impersonate.leave')
    ->middleware(config('filament-impersonate.leave_middleware'));
