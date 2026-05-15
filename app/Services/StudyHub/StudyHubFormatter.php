<?php

namespace App\Services\StudyHub;

use App\Models\Discussion;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudyResourceReview;
use App\Models\StudySession;
use App\Models\User;
use Illuminate\Support\Carbon;

class StudyHubFormatter
{
    public function discussion(Discussion $discussion): array
    {
        $authorName = $discussion->author?->display_name ?: $discussion->author?->name ?: 'Unknown';
        $images = $this->discussionImages($discussion);
        $firstImage = $images[0] ?? null;

        return [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'author' => $authorName,
            'author_avatar_url' => $discussion->author?->avatar_url ?: '',
            'author_initials' => $this->initials($authorName),
            'group_id' => $discussion->group_id,
            'group' => $discussion->group?->name ?? 'Unknown Group',
            'replies' => isset($discussion->replies_count)
                ? (int) $discussion->replies_count
                : ($discussion->relationLoaded('replies') ? $discussion->replies->count() : $discussion->replies()->count()),
            'helpful_votes' => isset($discussion->helpful_votes_count)
                ? (int) $discussion->helpful_votes_count
                : ($discussion->relationLoaded('helpfulVotes') ? $discussion->helpfulVotes->count() : $discussion->helpfulVotes()->count()),
            'viewer_voted_helpful' => auth()->check()
                && ($discussion->relationLoaded('helpfulVotes')
                    ? $discussion->helpfulVotes->isNotEmpty()
                    : $discussion->helpfulVotes()->where('user_id', auth()->id())->exists()),
            'views' => (int) $discussion->views,
            'last_active' => $this->humanizeTime($discussion->last_active_at ?: $discussion->updated_at ?: $discussion->created_at),
            'trending' => (bool) $discussion->trending,
            'body' => $discussion->body,
            'image_url' => $firstImage['url'] ?? '',
            'image_name' => $firstImage['name'] ?? '',
            'images' => $images,
            'image_count' => count($images),
            'has_image' => $images !== [],
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
        $fileType = $resource->file_type ?: strtolower(pathinfo($resource->name, PATHINFO_EXTENSION) ?: 'file');
        $viewerReview = $resource->relationLoaded('reviews') ? $resource->reviews->first() : null;
        $latestReview = $resource->relationLoaded('latestReview') ? $resource->latestReview : null;
        $viewerSave = $resource->relationLoaded('savedResources') ? $resource->savedResources->first() : null;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'category' => $resource->category,
            'group' => $resource->group?->name ?? 'Unknown Group',
            'file_type' => $fileType,
            'download_count' => (int) $resource->download_count,
            'rating_average' => (float) $resource->rating_average,
            'rating_count' => (int) $resource->rating_count,
            'is_saved' => (bool) $viewerSave,
            'saved_id' => $viewerSave?->id,
            'saved_folder_id' => $viewerSave?->resource_folder_id,
            'saved_folder' => $viewerSave?->folder?->name ?: '',
            'viewer_review' => $viewerReview ? $this->review($viewerReview) : null,
            'latest_review' => $latestReview ? $this->review($latestReview) : null,
            'size' => $this->fileSize((int) $resource->size_bytes),
            'date' => optional($resource->uploaded_at ?: $resource->created_at)->format('M j, Y'),
            'uploaded_by' => $uploaderName,
            'uploader_avatar_url' => $resource->uploader?->avatar_url ?: '',
            'uploader_initials' => $this->initials($uploaderName),
            'path' => $resource->path,
        ];
    }

    public function review(StudyResourceReview $review): array
    {
        $reviewerName = $review->reviewer?->display_name ?: $review->reviewer?->name ?: 'StudyHub Member';

        return [
            'id' => $review->id,
            'accuracy_rating' => (int) $review->accuracy_rating,
            'clarity_rating' => (int) $review->clarity_rating,
            'usefulness_rating' => (int) $review->usefulness_rating,
            'overall_rating' => $review->overallRating(),
            'review_text' => $review->review_text ?: '',
            'reviewer' => $reviewerName,
            'reviewer_initials' => $this->initials($reviewerName),
            'date' => optional($review->updated_at ?: $review->created_at)->format('M j, Y'),
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
            'group_id' => $session->group_id,
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

    private function discussionImages(Discussion $discussion): array
    {
        $images = collect($discussion->images ?: [])
            ->filter(fn ($image) => is_array($image) && ! empty($image['path']))
            ->values();

        if ($images->isEmpty() && $discussion->image_path) {
            $images = collect([[
                'path' => $discussion->image_path,
                'name' => $discussion->image_original_name ?: '',
                'mime_type' => $discussion->image_mime_type ?: '',
            ]]);
        }

        return $images
            ->map(fn (array $image, int $index) => [
                'url' => route('studyhub.student.discussions.images.show', [$discussion, $index]),
                'name' => (string) ($image['name'] ?? ''),
                'mime_type' => (string) ($image['mime_type'] ?? ''),
            ])
            ->all();
    }
}
