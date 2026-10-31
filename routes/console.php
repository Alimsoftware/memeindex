<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('xp:recalcular', function () {
    \App\Models\User::query()->each(fn($user) => $user->recalculateXp());
    $this->info('XP recalculado.');
})->purpose('Recalcula o XP de todos os usuários');
