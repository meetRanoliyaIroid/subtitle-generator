<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('videos.index');
});

Route::resource('videos', VideoController::class);
Route::get('videos/{video}/download-subtitle/{language?}', [VideoController::class, 'downloadSubtitle'])->name('videos.download-subtitle');
Route::get('videos/{video}/download-video', [VideoController::class, 'downloadVideo'])->name('videos.download-video');
