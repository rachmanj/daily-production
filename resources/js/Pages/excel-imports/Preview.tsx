import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Descriptions, Table, Tag } from 'antd';

interface ImportBatch {
    id: number;
    uuid: string;
    type: string;
    original_filename: string;
    status: string;
    parsed_payload?: Record<string, unknown>[] | null;
    row_errors?: { row: number; message: string }[] | null;
}

interface PreviewProps {
    batch: ImportBatch;
}

const STATUS_COLOR: Record<string, string> = {
    parsing: 'processing',
    preview: 'warning',
    committed: 'success',
    failed: 'error',
};

export default function Preview({ batch }: PreviewProps) {
    const rows = Array.isArray(batch.parsed_payload) ? batch.parsed_payload : [];
    const errors = batch.row_errors ?? [];

    const columns = rows.length > 0
        ? Object.keys(rows[0]).map((key) => ({
              title: key,
              dataIndex: key,
              key,
              ellipsis: true,
          }))
        : [];

    return (
        <AuthenticatedLayout title="Preview Import">
            <Head title="Preview Import" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions column={{ xs: 1, sm: 2 }}>
                    <Descriptions.Item label="File">{batch.original_filename}</Descriptions.Item>
                    <Descriptions.Item label="Tipe">{batch.type}</Descriptions.Item>
                    <Descriptions.Item label="Status">
                        <Tag color={STATUS_COLOR[batch.status] ?? 'default'}>{batch.status}</Tag>
                    </Descriptions.Item>
                    <Descriptions.Item label="Baris Valid">{rows.length}</Descriptions.Item>
                </Descriptions>
            </Card>

            {batch.status === 'parsing' && (
                <Alert type="info" message="File sedang diproses. Halaman akan di-refresh otomatis." showIcon />
            )}

            {errors.length > 0 && (
                <Card title="Error" style={{ marginBottom: 16 }}>
                    <Table
                        dataSource={errors}
                        rowKey="row"
                        size="small"
                        pagination={false}
                        columns={[
                            { title: 'Baris', dataIndex: 'row', key: 'row', width: 80 },
                            { title: 'Pesan', dataIndex: 'message', key: 'message' },
                        ]}
                    />
                </Card>
            )}

            {rows.length > 0 && (
                <Card title="Preview Data">
                    <Table
                        dataSource={rows.map((row, i) => ({ ...row, key: i }))}
                        columns={columns}
                        size="small"
                        scroll={{ x: true }}
                        pagination={{ pageSize: 20 }}
                    />
                </Card>
            )}

            {batch.status === 'preview' && (
                <Button
                    type="primary"
                    style={{ marginTop: 16 }}
                    onClick={() => router.post(route('excel-imports.confirm', batch.id))}
                >
                    Konfirmasi Import
                </Button>
            )}
        </AuthenticatedLayout>
    );
}
