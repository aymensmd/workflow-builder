import { Typography } from 'antd';
import BaseNode from './BaseNode';

export default function ActionNode(props) {
  return (
    <BaseNode {...props} showEdit>
      <Typography.Text>Action step</Typography.Text>
    </BaseNode>
  );
}
