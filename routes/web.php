<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PosController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/', [PosController::class, 'index'])->name('pos');
