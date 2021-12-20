<?php


use Illuminate\Support\Facades\Route;
use Telegram\Bot\Api;

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

    return view('welcome');
});


Route::resource('files',\App\Http\Controllers\FilesController::class);
Route::get('files/download/{id}',[\App\Http\Controllers\FilesController::class,'download'])->name('files.download');
Route::get('setChatID',[\App\Http\Controllers\FilesController::class,'setChatID'])->name('files.setChatID');
