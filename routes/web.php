<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MemeGallery;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Moderation;
use App\Livewire\Profile;
use App\Livewire\UploadMeme;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', MemeGallery::class)->name('home');
Route::get('/enviar', UploadMeme::class)->name('upload');
Route::view('/sobre', 'about')->name('about');
Route::redirect('/dmca', '/sobre#dmca');
Route::view('/privacidade', 'privacy')->name('privacy');
Route::view('/termos', 'terms')->name('terms');
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