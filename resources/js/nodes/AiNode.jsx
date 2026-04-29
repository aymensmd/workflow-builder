import { Typography } from 'antd';
import BaseNode from './BaseNode';

export default function AiNode(props) {
  return (
    <BaseNode {...props} showMagic showEdit>
      <Typography.Text>AI-assisted action</Typography.Text>
    </BaseNode>
  );
}
