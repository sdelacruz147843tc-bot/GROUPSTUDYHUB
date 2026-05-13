<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'study_resource_id',
        'resource_folder_id',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(StudyResource::class, 'study_resource_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ResourceFolder::class, 'resource_folder_id');
    }
}
