<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'is_gif' => 'boolean',
        'moderated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
