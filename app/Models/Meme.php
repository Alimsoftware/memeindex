<?php

namespace App\Models;


use App\Models\Vote;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Meme extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        static::creating(function (Meme $meme) {
            if (empty($meme->slug)) {
                $meme->slug = Str::slug($meme->title);
            }
        });
    }

    protected $casts = [
        'is_gif' => 'boolean',
        'moderated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

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
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->with(['user', 'children.user'])
            ->latest();
    }

    // Todos os comentários (raiz + respostas), para o chat e a contagem total.
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getUpvotesCountAttribute(): int
    {
        return $this->votes()->where('vote_type', 'upvote')->count();
    }

    public function getDownvotesCountAttribute(): int
    {
        return $this->votes()->where('vote_type', 'downvote')->count();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user)
            return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
