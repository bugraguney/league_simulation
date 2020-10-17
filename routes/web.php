<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/new_league', [HomeController::class, 'newLeague'])->name('new_league');
Route::post('/next_week', [HomeController::class, 'nextWeek'])->name('next_week');
Route::post('/play_all', [HomeController::class, 'playAll'])->name('play_all');
Route::get('/change_language/{lang}', [HomeController::class, 'changeLanguage'])->where(['lang' => 'tr|en'])->name('change_language');
