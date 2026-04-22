<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'display_name',
    'avatar_url',
    'bio',
    'theme',
    'surface_style',
    'interface_density',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function joinedGroups(): BelongsToMany
    {
        return $this->belongsToMany(StudyGroup::class, 'group_memberships', 'user_id', 'study_group_id')
            ->withTimestamps();
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class, 'owner_id');
    }

    public function uploadedResources(): HasMany
    {
        return $this->hasMany(StudyResource::class, 'uploaded_by');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class, 'author_id');
    }

    public function discussionReplies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'author_id');
    }

    public function createdSessions(): HasMany
    {
        return $this->hasMany(StudySession::class, 'created_by');
    }

    public function attendingSessions(): BelongsToMany
    {
        return $this->belongsToMany(StudySession::class, 'session_attendees', 'user_id', 'study_session_id')
            ->withTimestamps();
    }
}
