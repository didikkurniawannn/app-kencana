<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/secure-download', [\App\Http\Controllers\SecureFileController::class, 'download'])
    ->middleware('auth')
    ->name('secure.download');

Route::get('/archive/label/sp2d/{id}/print', [\App\Http\Controllers\ArchiveLabelController::class, 'printSp2dLabel'])
    ->middleware('auth')
    ->name('archive.label.sp2d.print');

Route::get('/archive/label/realisasi/{id}/print', [\App\Http\Controllers\ArchiveLabelController::class, 'printRealisasiLabel'])
    ->middleware('auth')
    ->name('archive.label.realisasi.print');

Route::get('/archive/register/print', [\App\Http\Controllers\ArchiveLabelController::class, 'printArchiveRegister'])
    ->middleware('auth')
    ->name('archive.register.print');
