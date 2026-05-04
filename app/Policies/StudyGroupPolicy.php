<?php

namespace App\Policies;

use App\Models\StudyGroup;
use App\Models\User;

class StudyGroupPolicy
{
    public function view(User $user, StudyGroup $studyGroup): bool
    {
        return $user->isAdmin()
            || $studyGroup->visibility === 'public'
            || $this->isMember($user, $studyGroup);
    }

    public function viewContent(User $user, StudyGroup $studyGroup): bool
    {
        return $user->isAdmin()
            || $studyGroup->visibility === 'public'
            || $this->isMember($user, $studyGroup);
    }

    public function createContent(User $user, StudyGroup $studyGroup): bool
    {
        return $user->isAdmin() || $this->isMember($user, $studyGroup);
    }

    private function isMember(User $user, StudyGroup $studyGroup): bool
    {
        return (int) $studyGroup->owner_id === (int) $user->id
            || $studyGroup->members()
                ->where('users.id', $user->id)
                ->exists();
    }
}
