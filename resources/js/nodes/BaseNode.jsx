import { Card, Button, Space } from 'antd';
import { Handle, Position } from 'reactflow';
import { Sparkles } from 'lucide-react';
import { EditOutlined } from '@ant-design/icons';
import { useWorkflowStore } from '../store/store';

export default function BaseNode({ id, data, children, color = '#4096ff', hasTarget = true, hasSource = true, showMagic = false, showEdit = false }) {
  const { selectNode } = useWorkflowStore();

  return (
    <Card
      size="small"
      style={{ minWidth: 240, borderColor: color, boxShadow: '0 4px 14px rgba(0,0,0,0.18)' }}
      title={data?.label || 'Node'}
      extra={
        <Space>
          {showMagic && (
            <Button size="small" type="text" icon={<Sparkles size={16} />} onClick={() => selectNode({ id, data })} />
          )}
          {showEdit && (
            <Button size="small" type="text" icon={<EditOutlined />} onClick={() => selectNode({ id, data })} />
          )}
        </Space>
      }
    >
      {hasTarget && <Handle type="target" position={Position.Left} />}
      {children}
      {hasSource && <Handle type="source" position={Position.Right} />}
    </Card>
  );
}
