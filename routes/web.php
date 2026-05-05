<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FactureController;
/*
Route::get('/', function () {
//return "coca cola";    
return view('caca');
});
*/
Route::get('/facture', [FactureController::class, 'showForm'])->name('facture.form');
Route::post('/facture', [FactureController::class, 'generate'])->name('facture.generate');
