# Workflow Builder Backend Blueprint (Laravel 11)

This repository now contains a modular backend foundation for a multi-tenant workflow engine.

## Key Architecture Notes

- **Multi-tenancy guardrail**: top-level models (`Workflow`, `Credential`) include `workspace_id` and use a reusable `forWorkspace()` scope trait.
- **Versioned canvas state**: `WorkflowVersion` stores immutable `nodes` + `edges` snapshots so each save creates a new version.
- **Queue-first execution**: each node runs in `ExecuteWorkflowNode` (`ShouldQueue`) to prevent long DAG runs from timing out web requests.
- **Execution observability**: each node run writes to `execution_logs` (input/output/error) to support replay and debugging.

## Node Data Passing Strategy (A -> B)

1. A node receives `inputData` payload from its upstream node.
2. `NodeRunnerService` executes the node and returns normalized output:
   - `status` (`success`/`failed`)
   - `data` (the payload for downstream nodes)
3. `ExecuteWorkflowNode` dispatches jobs for all outgoing edges where `source === currentNode.id`.
4. Each downstream job receives the upstream output as its `inputData`.

This gives you deterministic payload propagation without coupling node logic to the queue/job layer.

## Why queues prevent timeout issues

- HTTP requests should return quickly (trigger endpoint responds with `202` immediately).
- Heavy node workloads (external APIs, retries, AI calls) run in queue workers (Horizon + Redis).
- Failures are isolated per node run and can be retried independently.
- System can scale horizontally by adding workers without changing API behavior.
