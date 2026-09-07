<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MemeGallery;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', MemeGallery::class)->name('home');


Route::get('/media/memes/{filename}', function ($filename) {
    $path = 'memes/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(Storage::disk('public')->path($path));
})->where('filename', '.*');