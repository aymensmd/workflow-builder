<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'nodes',
        'edges',
        'version_number',
        'is_published',
    ];

    protected $casts = [
        'nodes' => 'array',
        'edges' => 'array',
        'is_published' => 'boolean',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
