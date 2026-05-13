<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudyResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'uploaded_by',
        'name',
        'category',
        'path',
        'mime_type',
        'file_type',
        'download_count',
        'rating_average',
        'rating_count',
        'size_bytes',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class, 'group_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StudyResourceReview::class);
    }

    public function savedResources(): HasMany
    {
        return $this->hasMany(SavedResource::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ResourceView::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(StudyResourceReview::class)->latestOfMany();
    }
}
