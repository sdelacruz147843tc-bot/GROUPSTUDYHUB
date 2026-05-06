<?php

namespace App\Policies;

use App\Models\StudyResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class StudyResourcePolicy
{
    public function view(User $user, StudyResource $studyResource): bool
    {
        return $studyResource->group !== null
            && Gate::forUser($user)->allows('viewContent', $studyResource->group);
    }

    public function create(User $user, StudyResource $studyResource): bool
    {
        return $studyResource->group !== null
            && Gate::forUser($user)->allows('createContent', $studyResource->group);
    }

    public function delete(User $user, StudyResource $studyResource): bool
    {
        return $user->isAdmin()
            || (int) $studyResource->uploaded_by === (int) $user->id
            || (int) $studyResource->group?->owner_id === (int) $user->id;
    }
}
