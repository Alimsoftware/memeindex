<?php

namespace App\Models;

use App\Models\Favorite;
use App\Models\Comment;
use App\Models\Share;
use App\Models\Vote;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        // editar posts é só de mod plus e admin; mod simples fica na /moderacao
        return $this->canPostDirectly();
    }

    // pontos por ação; XP só indica candidatos, a promoção é aprovada por admin ou pelo dono
    public const XP = [
        'comment' => 2,
        'vote' => 1,
        'upvote_received' => 5,
        'downvote_received' => -2,
        'share' => 3,
    ];

    // recalcula a partir dos dados: votar/desvotar ou apagar comentário não acumula pontos
    public function recalculateXp(): void
    {
        $received = Vote::whereHas('meme', fn($q) => $q->where('user_id', $this->id))
            ->where('user_id', '!=', $this->id);

        $xp = Comment::where('user_id', $this->id)->count() * self::XP['comment']
            + Vote::where('user_id', $this->id)->count() * self::XP['vote']
            + (clone $received)->where('vote_type', 'upvote')->count() * self::XP['upvote_received']
            + (clone $received)->where('vote_type', 'downvote')->count() * self::XP['downvote_received']
            + Share::where('user_id', $this->id)->count() * self::XP['share'];

        $this->forceFill(['xp_points' => max(0, $xp)])->saveQuietly();
    }

    public function canPostDirectly(): bool
    {
        // admin, ou moderador promovido (data em can_post_without_moderation_at);
        // quem publica direto também aprova envios na moderação
        return $this->role === 'admin'
            || ($this->role === 'moderator' && $this->can_post_without_moderation_at !== null);
    }

    public const LEVELS = [
        'user' => 'User',
        'mod' => 'Mod simples',
        'mod_plus' => 'Mod plus',
        'admin' => 'Admin',
    ];

    // nível na hierarquia, derivado de role + can_post_without_moderation_at
    public function level(): string
    {
        return match (true) {
            $this->role === 'admin' => 'admin',
            $this->role === 'moderator' && $this->can_post_without_moderation_at !== null => 'mod_plus',
            $this->role === 'moderator' => 'mod',
            default => 'user',
        };
    }

    // colunas a gravar para um nível; mantém a data original de promoção a mod plus
    public function attributesForLevel(string $level): array
    {
        return [
            'role' => match ($level) {
                'admin' => 'admin',
                'mod', 'mod_plus' => 'moderator',
                default => 'user',
            },
            'can_post_without_moderation_at' => $level === 'mod_plus'
                ? ($this->can_post_without_moderation_at ?? now())
                : null,
        ];
    }

    // dono mexe em todos menos em si; admin só em quem está abaixo de admin
    public function canManage(User $other): bool
    {
        if ($this->is($other)) {
            return false;
        }

        return $this->isOwner() || ($this->role === 'admin' && $other->level() !== 'admin' && !$other->isOwner());
    }

    public function isOwner(): bool
    {
        return filled(config('app.owner_email')) && $this->email === config('app.owner_email');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}