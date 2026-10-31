<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    protected $fillable = [
        'user_id',
        'meme_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meme(): BelongsTo
    {
        return $this->belongsTo(Meme::class);
    }
}
