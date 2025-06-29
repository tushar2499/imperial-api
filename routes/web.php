<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


//Route::get('login',  [AuthController::class, 'login'])->name('login');
//Route::post('login', [AuthController::class, 'login']);
//Route::post('logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/clear', function () {
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('event:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('optimize:clear');
    \Artisan::call('optimize');

    return "Clear";
});
