<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MemeGallery;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Moderation;
use App\Livewire\Profile;
use App\Livewire\UploadMeme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', MemeGallery::class)->name('home');
Route::get('/enviar', UploadMeme::class)->name('upload');
Route::view('/sobre', 'about')->name('about');
Route::redirect('/dmca', '/sobre#dmca');
Route::get('/perfil', Profile::class)->middleware('auth')->name('profile');
Route::get('/moderacao', Moderation::class)->middleware('auth')->name('moderation');

Route::middleware('guest')->group(function () {
    Route::get('/entrar', Login::class)->name('login');
    Route::get('/cadastrar', Register::class)->name('register');
});

Route::post('/sair', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');


Route::get('/media/memes/{filename}', function ($filename) {
    $path = 'memes/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return response()->file(Storage::disk('public')->path($path));
})->where('filename', '.*');