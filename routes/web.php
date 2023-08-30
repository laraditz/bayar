<?php

use Illuminate\Support\Facades\Route;
use Laraditz\Bayar\Http\Controllers\BayarController;

Route::get('/pay/{payment}', [BayarController::class, 'pay'])->name('pay');
Route::match(['get', 'post'], '/done/{payment}', [BayarController::class, 'done'])->name('done');
Route::post('/{provider}/callback', [BayarController::class, 'callback'])->name('callback');
