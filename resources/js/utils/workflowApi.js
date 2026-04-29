import axios from 'axios';

/**
 * Persist the current React Flow graph as a new workflow version.
 */
export async function saveWorkflowVersion({ workflowId, workspaceId, nodes, edges, isPublished = false }) {
  const response = await axios.post(
    `/api/workflows/${workflowId}/versions`,
    {
      nodes,
      edges,
      is_published: isPublished,
    },
    {
      headers: {
        'X-Workspace-Id': workspaceId,
      },
    }
  );

  return response.data;
}
