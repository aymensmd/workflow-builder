<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExecuteWorkflowNode;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowExecutionController extends Controller
{
    public function trigger(Request $request, Workflow $workflow): JsonResponse
    {
        $workspaceId = (int) $request->header('X-Workspace-Id');

        abort_unless($workflow->workspace_id === $workspaceId, 403, 'Workspace mismatch.');

        $version = $workflow->versions()->where('is_published', true)->latest('version_number')->firstOrFail();

        $triggerNode = collect($version->nodes)->first(fn (array $node) => data_get($node, 'type') === 'trigger');
        abort_unless($triggerNode, 422, 'No trigger node found in workflow version.');

        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        ExecuteWorkflowNode::dispatch(
            executionId: $execution->id,
            node: $triggerNode,
            allEdges: $version->edges,
            allNodes: $version->nodes,
            inputData: ['triggered_at' => now()->toIso8601String()]
        );

        return response()->json([
            'message' => 'Workflow execution queued.',
            'execution_id' => $execution->id,
        ], 202);
    }
}
