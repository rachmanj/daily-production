<?php

namespace App\Policies;

use App\Enums\EntryStatus;
use App\Models\DailyEntry;
use App\Models\User;

class DailyEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('entry.create') || $user->can('entry.approve') || $user->can('dashboard.view');
    }

    public function view(User $user, DailyEntry $dailyEntry): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('entry.create');
    }

    public function update(User $user, DailyEntry $dailyEntry): bool
    {
        if ($dailyEntry->status !== EntryStatus::Draft) {
            return false;
        }

        return $user->can('entry.create') && (
            $user->can('entry.approve') || $dailyEntry->created_by === $user->id
        );
    }

    public function delete(User $user, DailyEntry $dailyEntry): bool
    {
        return $dailyEntry->status === EntryStatus::Draft
            && ($user->can('entry.approve') || $dailyEntry->created_by === $user->id);
    }

    public function submit(User $user, DailyEntry $dailyEntry): bool
    {
        return $dailyEntry->status === EntryStatus::Draft && $user->can('entry.create');
    }

    public function approve(User $user, DailyEntry $dailyEntry): bool
    {
        return $dailyEntry->status === EntryStatus::Submitted && $user->can('entry.approve');
    }

    public function reject(User $user, DailyEntry $dailyEntry): bool
    {
        return $dailyEntry->status === EntryStatus::Submitted && $user->can('entry.approve');
    }
}
