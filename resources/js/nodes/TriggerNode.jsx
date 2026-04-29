import { Typography } from 'antd';
import BaseNode from './BaseNode';

export default function TriggerNode(props) {
  return (
    <BaseNode {...props} color="#52c41a" hasTarget={false} hasSource>
      <Typography.Text>Starts the workflow</Typography.Text>
    </BaseNode>
  );
}
