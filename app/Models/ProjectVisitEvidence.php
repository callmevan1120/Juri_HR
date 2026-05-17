<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVisitEvidence extends Model
{
    protected $table = 'project_visit_evidences';

    protected $fillable = [
        'company_id',
        'project_id',
        'project_task_id',
        'user_id',
        'visited_at',
        'latitude',
        'longitude',
        'accuracy_meters',
        'address',
        'notes',
        'photo_disk',
        'photo_path',
        'photo_original_name',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
