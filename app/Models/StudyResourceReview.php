<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyResourceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_resource_id',
        'user_id',
        'accuracy_rating',
        'clarity_rating',
        'usefulness_rating',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'accuracy_rating' => 'integer',
            'clarity_rating' => 'integer',
            'usefulness_rating' => 'integer',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(StudyResource::class, 'study_resource_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function overallRating(): float
    {
        return round(($this->accuracy_rating + $this->clarity_rating + $this->usefulness_rating) / 3, 2);
    }
}
