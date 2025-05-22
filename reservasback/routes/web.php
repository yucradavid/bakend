<?php

use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
// routes/web.php
Route::get('sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token set'])->withCookie(cookie('XSRF-TOKEN', csrf_token()));
});
