<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function show(Request $request, Workflow $workflow): JsonResponse
    {
        $workspaceId = (int) $request->header('X-Workspace-Id');
        abort_unless($workflow->workspace_id === $workspaceId, 403, 'Workspace mismatch.');

        $workflow->load(['versions' => fn ($q) => $q->latest('version_number')]);

        return response()->json($workflow);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workspace_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workflow = Workflow::create($validated);

        return response()->json($workflow, 201);
    }

    public function update(Request $request, Workflow $workflow): JsonResponse
    {
        $workspaceId = (int) $request->header('X-Workspace-Id');
        abort_unless($workflow->workspace_id === $workspaceId, 403, 'Workspace mismatch.');

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $workflow->update($validated);

        return response()->json($workflow);
    }
}
