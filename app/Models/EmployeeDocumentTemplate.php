<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDocumentTemplate extends Model
{
    protected $fillable = [
        'document_type_id',
        'name',
        'paper_size',
        'orientation',
        'body',
        'footer',
        'layout_options',
        'is_active',
        'is_marketplace',
        'marketplace_slug',
        'marketplace_category',
        'marketplace_tags',
        'source_template_id',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_marketplace' => 'boolean',
        'layout_options' => 'array',
        'marketplace_tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(EmployeeDocumentType::class, 'document_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function generatedRequests(): HasMany
    {
        return $this->hasMany(EmployeeDocumentRequest::class, 'generated_template_id');
    }

    public function sourceTemplate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_template_id');
    }
}
