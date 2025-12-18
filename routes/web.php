<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('videos', VideoController::class);
Route::get('videos/{video}/download-subtitle', [VideoController::class, 'downloadSubtitle'])->name('videos.download-subtitle');
Route::get('videos/{video}/download-video', [VideoController::class, 'downloadVideo'])->name('videos.download-video');
Route::get('videos/{video}/status', [VideoController::class, 'getStatus'])->name('videos.status');
