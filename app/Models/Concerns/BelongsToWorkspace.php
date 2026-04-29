<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToWorkspace
{
    public function scopeForWorkspace(Builder $query, int $workspaceId): Builder
    {
        return $query->where($this->getTable() . '.workspace_id', $workspaceId);
    }
}
