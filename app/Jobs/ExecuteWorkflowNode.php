<?php

namespace App\Jobs;

use App\Models\ExecutionLog;
use App\Models\WorkflowExecution;
use App\Services\NodeRunnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowNode implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $executionId,
        public array $node,
        public array $allEdges,
        public array $allNodes,
        public array $inputData = []
    ) {
    }

    public function handle(NodeRunnerService $nodeRunnerService): void
    {
        $execution = WorkflowExecution::findOrFail($this->executionId);

        $log = ExecutionLog::create([
            'execution_id' => $execution->id,
            'node_id' => (string) data_get($this->node, 'id'),
            'status' => 'running',
            'input_data' => $this->inputData,
        ]);

        $result = $nodeRunnerService->run($this->node, $this->inputData);

        $log->update([
            'status' => $result['status'] === 'success' ? 'success' : 'failed',
            'output_data' => $result['data'] ?? null,
            'error_message' => $result['error'] ?? null,
        ]);

        if (($result['status'] ?? 'failed') !== 'success') {
            $execution->update(['status' => 'failed', 'completed_at' => now()]);
            return;
        }

        $nodeId = data_get($this->node, 'id');
        $nextNodeIds = collect($this->allEdges)
            ->where('source', $nodeId)
            ->pluck('target')
            ->values();

        if ($nextNodeIds->isEmpty()) {
            $execution->update(['status' => 'success', 'completed_at' => now()]);
            return;
        }

        foreach ($nextNodeIds as $nextNodeId) {
            $nextNode = collect($this->allNodes)->firstWhere('id', $nextNodeId);
            if (!$nextNode) {
                continue;
            }

            self::dispatch(
                executionId: $execution->id,
                node: $nextNode,
                allEdges: $this->allEdges,
                allNodes: $this->allNodes,
                inputData: $result['data'] ?? []
            );
        }
    }
}
