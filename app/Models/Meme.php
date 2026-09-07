<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function getUpvotesCountAttribute(): int
    {
        return $this->votes()->where('vote_type', 'upvote')->count();
    }

    public function getDownvotesCountAttribute(): int
    {
        return $this->votes()->where('vote_type', 'downvote')->count();
    }
}
