<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_id',
        'node_id',
        'status',
        'input_data',
        'output_data',
        'error_message',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class, 'execution_id');
    }
}
