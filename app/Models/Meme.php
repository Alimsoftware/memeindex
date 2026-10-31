<?php

namespace App\Models;


use App\Models\Vote;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Meme extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'geral',
        'engracado',
        'politico',
        'animais',
        'trabalho',
        'esporte'
    ];

    public const VOTE_TYPES = ['up', 'down'];
    public const REPORT_REASONS = ['inappropriate', 'duplicated', 'offensive', 'spam', 'copyright', 'other'];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'image_path',
        'description',
        'category',
        'is_gif',
        'status',
        'rejection_reason',
        'moderated_by',
        'moderated_at',
        'views',
        'downloads',
        'published_at',
    ];

    protected $casts = [
        'is_gif' => 'boolean',
        'moderated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Meme $meme) {
            if (empty($meme->slug)) {
                $meme->slug = Str::slug($meme->title) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    // Meme.php
    public function getImageUrlAttribute(): string
    {
        // Gera uma URL válida por 30 minutos, por exemplo
        return Storage::disk('b2')->temporaryUrl(
            $this->image_path,
            now()->addMinutes(30)
        );
    }

    // o atributo download do <a> é ignorado em outro domínio: o próprio B2 manda baixar
    public function getDownloadUrlAttribute(): string
    {
        $name = $this->slug . '.' . pathinfo($this->image_path, PATHINFO_EXTENSION);

        return Storage::disk('b2')->temporaryUrl($this->image_path, now()->addMinutes(30), [
            'ResponseContentDisposition' => 'attachment; filename="' . $name . '"',
        ]);
    }

    // link permanente para compartilhar (o image_url expira)
    public function getPageUrlAttribute(): string
    {
        return route('home', ['meme' => $this->slug]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        // Retorna apenas comentários raiz, trazendo os filhos (respostas) ordenados
        return $this->hasMany(Comment::class);
    }

    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }



    public function isFavoritedBy(?User $user): bool
    {
        if (!$user)
            return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }
    public function getScore(): int
    {
        return $this->votes()->where('vote_type', 'upvote')->count()
            - $this->votes()->where('vote_type', 'downvote')->count();
    }
}
