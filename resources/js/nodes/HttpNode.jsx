import { Typography } from 'antd';
import BaseNode from './BaseNode';

export default function HttpNode(props) {
  return (
    <BaseNode {...props} showEdit>
      <Typography.Text type="secondary">{props?.data?.config?.method || 'GET'}</Typography.Text>
      <br />
      <Typography.Text ellipsis style={{ maxWidth: 190 }}>
        {props?.data?.config?.url || 'https://api.example.com'}
      </Typography.Text>
    </BaseNode>
  );
}
