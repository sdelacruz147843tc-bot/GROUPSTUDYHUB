<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'parent_reply_id',
        'author_id',
        'body',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parentReply(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_reply_id');
    }

    public function childReplies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_reply_id');
    }
}
