import { useMemo, useState } from 'react';
import ReactFlow, { Background, Controls, MiniMap } from 'reactflow';
import 'reactflow/dist/style.css';
import { Layout, Button, Space, Drawer, Form, Input, Select, theme, message } from 'antd';
import { PlusCircleOutlined, PlayCircleOutlined, SaveOutlined, RobotOutlined } from '@ant-design/icons';
import { useWorkflowStore } from '../store/store';
import TriggerNode from '../nodes/TriggerNode';
import HttpNode from '../nodes/HttpNode';
import ActionNode from '../nodes/ActionNode';
import AiNode from '../nodes/AiNode';

const { Header, Sider, Content } = Layout;

export default function WorkflowCanvas() {
  const {
    nodes,
    edges,
    selectedNode,
    drawerOpen,
    onNodesChange,
    onEdgesChange,
    onConnect,
    addNode,
    selectNode,
    closeDrawer,
    updateNodeData,
    saveWorkflow,
    executeWorkflow,
    runNodeTest,
  } = useWorkflowStore();

  const [form] = Form.useForm();
  const [api, contextHolder] = message.useMessage();
  const { token } = theme.useToken();

  const nodeTypes = useMemo(
    () => ({
      trigger: TriggerNode,
      http_request: HttpNode,
      slack_action: ActionNode,
      ai_assistant: AiNode,
    }),
    []
  );

  const addNodeFromToolbar = (type) => addNode(type, { x: 300 + Math.random() * 200, y: 120 + Math.random() * 200 });

  const onNodeClick = (_, node) => {
    selectNode(node);
    form.setFieldsValue(node.data?.config || {});
  };

  const onDrawerSave = async () => {
    if (!selectedNode) return;
    const values = await form.validateFields();
    updateNodeData(selectedNode.id, { config: values });
    closeDrawer();
  };

  const handleSaveWorkflow = async () => {
    await saveWorkflow();
    api.success('Workflow saved as new version.');
  };

  const handleExecuteWorkflow = async () => {
    await executeWorkflow();
    api.success('Workflow execution queued.');
  };

  const handleRunTest = async () => {
    if (!selectedNode) return;
    await runNodeTest(selectedNode.id);
    api.info('Test run queued for selected node.');
  };

  return (
    <Layout style={{ height: '100vh', background: token.colorBgLayout }}>
      {contextHolder}
      <Header style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 8, position: 'sticky', top: 0, zIndex: 99 }}>
        <Button icon={<SaveOutlined />} type="primary" onClick={handleSaveWorkflow}>Save</Button>
        <Button icon={<PlayCircleOutlined />} onClick={handleExecuteWorkflow}>Execute</Button>
      </Header>
      <Layout>
        <Sider width={240} theme="light" style={{ borderRight: `1px solid ${token.colorBorderSecondary}`, padding: 12 }}>
          <Space direction="vertical" style={{ width: '100%' }}>
            <Button block icon={<PlusCircleOutlined />} onClick={() => addNodeFromToolbar('trigger')}>Add Trigger</Button>
            <Button block icon={<PlusCircleOutlined />} onClick={() => addNodeFromToolbar('http_request')}>Add HTTP</Button>
            <Button block icon={<PlusCircleOutlined />} onClick={() => addNodeFromToolbar('slack_action')}>Add Action</Button>
            <Button block icon={<RobotOutlined />} onClick={() => addNodeFromToolbar('ai_assistant')}>Add AI</Button>
          </Space>
        </Sider>
        <Content>
          <ReactFlow
            nodes={nodes}
            edges={edges}
            nodeTypes={nodeTypes}
            onNodesChange={onNodesChange}
            onEdgesChange={onEdgesChange}
            onConnect={onConnect}
            onNodeClick={onNodeClick}
            fitView
            snapToGrid
            snapGrid={[20, 20]}
          >
            <Background variant="dots" gap={18} size={1} />
            <MiniMap pannable zoomable />
            <Controls />
          </ReactFlow>
        </Content>
      </Layout>

      <Drawer
        title={`Configure ${selectedNode?.data?.label || 'Node'}`}
        placement="right"
        open={drawerOpen}
        onClose={closeDrawer}
        width={420}
        extra={<Button onClick={onDrawerSave} type="primary">Save Config</Button>}
      >
        {selectedNode?.type === 'http_request' ? (
          <Form form={form} layout="vertical" initialValues={selectedNode?.data?.config || { method: 'GET', url: '', headers: [] }}>
            <Form.Item label="URL" name="url" rules={[{ required: true, message: 'Please enter request URL' }]}>
              <Input placeholder="https://api.example.com" />
            </Form.Item>

            <Form.Item label="Method" name="method" rules={[{ required: true }]}>
              <Select options={['GET', 'POST', 'PUT', 'PATCH', 'DELETE'].map((value) => ({ value, label: value }))} />
            </Form.Item>

            <Form.List name="headers">
              {(fields, { add, remove }) => (
                <Space direction="vertical" style={{ width: '100%' }}>
                  {fields.map((field) => (
                    <Space key={field.key} align="baseline" style={{ display: 'flex' }}>
                      <Form.Item name={[field.name, 'key']} rules={[{ required: true }]}>
                        <Input placeholder="Header key" />
                      </Form.Item>
                      <Form.Item name={[field.name, 'value']} rules={[{ required: true }]}>
                        <Input placeholder="Header value" />
                      </Form.Item>
                      <Button danger onClick={() => remove(field.name)}>Remove</Button>
                    </Space>
                  ))}
                  <Button onClick={() => add({ key: '', value: '' })}>Add Header</Button>
                </Space>
              )}
            </Form.List>

            <Button style={{ marginTop: 16 }} onClick={handleRunTest}>Run Test</Button>
          </Form>
        ) : (
          <p>Select an HTTP node to configure request options.</p>
        )}
      </Drawer>
    </Layout>
  );
}
