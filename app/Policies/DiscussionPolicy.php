<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DiscussionPolicy
{
    public function view(User $user, Discussion $discussion): bool
    {
        return $discussion->group !== null
            && Gate::forUser($user)->allows('viewContent', $discussion->group);
    }

    public function reply(User $user, Discussion $discussion): bool
    {
        return $discussion->group !== null
            && Gate::forUser($user)->allows('createContent', $discussion->group);
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        return $user->isAdmin() || (int) $discussion->author_id === (int) $user->id;
    }
}
