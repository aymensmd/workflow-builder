<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowVersionController extends Controller
{
    public function store(Request $request, Workflow $workflow): JsonResponse
    {
        $workspaceId = (int) $request->header('X-Workspace-Id');
        abort_unless($workflow->workspace_id === $workspaceId, 403, 'Workspace mismatch.');

        $validated = $request->validate([
            'nodes' => 'required|array|min:1',
            'edges' => 'required|array',
            'is_published' => 'boolean',
        ]);

        $nextVersion = ((int) $workflow->versions()->max('version_number')) + 1;

        $version = WorkflowVersion::create([
            'workflow_id' => $workflow->id,
            'nodes' => $validated['nodes'],
            'edges' => $validated['edges'],
            'version_number' => $nextVersion,
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        return response()->json($version, 201);
    }
}
