<?php

namespace App\Services\StudyHub;

use App\Models\Discussion;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Carbon;

class StudyHubFormatter
{
    public function discussion(Discussion $discussion): array
    {
        $authorName = $discussion->author?->display_name ?: $discussion->author?->name ?: 'Unknown';

        return [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'author' => $authorName,
            'author_avatar_url' => $discussion->author?->avatar_url ?: '',
            'author_initials' => $this->initials($authorName),
            'group' => $discussion->group?->name ?? 'Unknown Group',
            'replies' => isset($discussion->replies_count)
                ? (int) $discussion->replies_count
                : ($discussion->relationLoaded('replies') ? $discussion->replies->count() : $discussion->replies()->count()),
            'views' => (int) $discussion->views,
            'last_active' => $this->humanizeTime($discussion->last_active_at ?: $discussion->updated_at ?: $discussion->created_at),
            'trending' => (bool) $discussion->trending,
            'body' => $discussion->body,
        ];
    }

    public function group(StudyGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'members' => $group->members_count,
            'resources' => $group->resources_count,
            'description' => $group->description,
            'color' => $group->color,
            'category' => $group->category,
            'meeting_style' => $group->meeting_style,
            'initial' => strtoupper(substr($group->name, 0, 1)),
            'visibility' => $group->visibility,
        ];
    }

    public function resource(StudyResource $resource): array
    {
        $uploaderName = $resource->uploader?->display_name ?: $resource->uploader?->name ?: 'Unknown';

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'category' => $resource->category,
            'group' => $resource->group?->name ?? 'Unknown Group',
            'size' => $this->fileSize((int) $resource->size_bytes),
            'date' => optional($resource->uploaded_at ?: $resource->created_at)->format('M j, Y'),
            'uploaded_by' => $uploaderName,
            'uploader_avatar_url' => $resource->uploader?->avatar_url ?: '',
            'uploader_initials' => $this->initials($uploaderName),
            'path' => $resource->path,
        ];
    }

    public function session(StudySession $session): array
    {
        $start = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->start_time);
        $end = Carbon::parse($session->session_date->format('Y-m-d').' '.$session->end_time);
        $phase = $end->isPast() ? 'past' : 'upcoming';
        $attendeeCount = isset($session->attendees_count)
            ? (int) $session->attendees_count
            : ($session->relationLoaded('attendees') ? $session->attendees->count() : $session->attendees()->count());
        $meetingUrl = $session->meeting_url ?: ($session->type === 'online' && filter_var($session->location, FILTER_VALIDATE_URL) ? $session->location : null);

        return [
            'id' => $session->id,
            'title' => $session->title,
            'group' => $session->group?->name ?? 'Unknown Group',
            'date' => $session->session_date->format('M j, Y'),
            'time' => $start->format('g:i A').' - '.$end->format('g:i A'),
            'location' => $session->type === 'online' ? 'Online meeting' : $session->location,
            'meeting_url' => $meetingUrl ?: '',
            'type' => $session->type,
            'attendees' => $attendeeCount,
            'max_attendees' => $session->max_attendees,
            'status' => $session->status,
            'phase' => $phase,
            'created_by' => $session->creator?->display_name ?: $session->creator?->name ?: 'Unknown',
            'attendee_names' => $session->relationLoaded('attendees')
                ? $session->attendees
                    ->map(fn (User $user) => $user->display_name ?: $user->name)
                    ->values()
                    ->all()
                : [],
            'notes' => $session->notes ?: '',
        ];
    }

    public function humanizeTime(Carbon|string|null $value): string
    {
        if (! $value) {
            return 'Just now';
        }

        $time = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $time->diffForHumans();
    }

    public function fileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).' MB';
        }

        return round($bytes / 1024).' KB';
    }

    private function initials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return '??';
        }

        return $parts
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
