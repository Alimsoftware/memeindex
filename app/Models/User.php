<?php

namespace App\Models;

use App\Models\Favorite;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'xp_points',
        'memes_submitted',
        'memes_approved',
        'can_post_without_moderation_at',
        'role',
    ];

    /**
     * Os atributos que devem ser ocultados na serialização.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'can_post_without_moderation_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define quem pode acessar o painel do Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // com cadastro público, só equipe acessa o admin
        return in_array($this->role, ['moderator', 'admin'], true);
    }

    public function addXp(int $amount)
    {
        $this->xp_points += $amount;

        if (method_exists($this, 'checkForAchievements')) {
            $this->checkForAchievements();
        }

        if ($this->xp_points >= 1000 && !$this->can_post_without_moderation_at) {
            $this->can_post_without_moderation_at = now();
        }

        $this->save();
    }

    public function canPostDirectly(): bool
    {
        return $this->can_post_without_moderation_at !== null || in_array($this->role, ['moderator', 'admin']);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}