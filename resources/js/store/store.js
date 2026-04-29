import { create } from 'zustand';
import { applyEdgeChanges, applyNodeChanges, addEdge } from 'reactflow';
import axios from 'axios';

const createId = (prefix) => `${prefix}_${crypto.randomUUID()}`;

export const useWorkflowStore = create((set, get) => ({
  workflowId: null,
  workspaceId: null,
  nodes: [],
  edges: [],
  selectedNode: null,
  drawerOpen: false,

  initialize({ workflowId, workspaceId, nodes = [], edges = [] }) {
    set({ workflowId, workspaceId, nodes, edges });
  },

  onNodesChange(changes) {
    set({ nodes: applyNodeChanges(changes, get().nodes) });
  },

  onEdgesChange(changes) {
    set({ edges: applyEdgeChanges(changes, get().edges) });
  },

  onConnect(connection) {
    set({ edges: addEdge({ ...connection, animated: true }, get().edges) });
  },

  addNode(type, position) {
    const id = createId(type);
    const defaults = {
      trigger: { label: 'Trigger', config: {} },
      http_request: { label: 'HTTP Request', config: { method: 'GET', url: '', headers: [] } },
      slack_action: { label: 'Slack Action', config: {} },
      ai_assistant: { label: 'AI Assistant', config: { prompt: '' } },
    };

    const node = {
      id,
      type,
      position,
      data: defaults[type] ?? { label: 'Custom Node', config: {} },
    };

    set({ nodes: [...get().nodes, node] });
    return node;
  },

  selectNode(node) {
    set({ selectedNode: node, drawerOpen: !!node });
  },

  closeDrawer() {
    set({ drawerOpen: false });
  },

  updateNodeData(id, newData) {
    const nodes = get().nodes.map((node) =>
      node.id === id
        ? { ...node, data: { ...node.data, ...newData, config: { ...(node.data?.config || {}), ...(newData?.config || {}) } } }
        : node
    );

    const selectedNode = nodes.find((node) => node.id === get().selectedNode?.id) || null;
    set({ nodes, selectedNode });
  },

  async saveWorkflow(id) {
    const state = get();
    const workflowId = id ?? state.workflowId;

    const response = await axios.post(
      `/api/workflows/${workflowId}/versions`,
      {
        nodes: state.nodes,
        edges: state.edges,
        is_published: true,
      },
      { headers: { 'X-Workspace-Id': state.workspaceId } }
    );

    return response.data;
  },

  async executeWorkflow(id) {
    const state = get();
    const workflowId = id ?? state.workflowId;

    const response = await axios.post(
      `/api/workflows/${workflowId}/execute`,
      {},
      { headers: { 'X-Workspace-Id': state.workspaceId } }
    );

    return response.data;
  },

  async runNodeTest(nodeId) {
    const state = get();
    const workflowId = state.workflowId;

    return axios.post(
      `/api/workflows/${workflowId}/execute`,
      { test_node_id: nodeId },
      { headers: { 'X-Workspace-Id': state.workspaceId } }
    );
  },
}));
