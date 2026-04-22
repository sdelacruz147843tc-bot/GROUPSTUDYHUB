<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'meeting_style',
        'visibility',
        'join_code',
        'color',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_memberships')
            ->withTimestamps();
    }

    public function resources(): HasMany
    {
        return $this->hasMany(StudyResource::class, 'group_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class, 'group_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(StudySession::class, 'group_id');
    }
}
