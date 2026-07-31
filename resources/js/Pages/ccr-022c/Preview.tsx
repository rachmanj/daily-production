import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Alert, Button, Card, Descriptions, List, Tag } from 'antd';

interface ParsedPayload {
    production_date?: string;
    preview?: {
        trip_count?: number;
        ob_bcm?: number;
        coal_ton?: number;
    };
    unmatched_codes?: string[];
}

interface ImportBatch {
    id: number;
    uuid: string;
    type: string;
    original_filename: string;
    status: string;
    parsed_payload?: ParsedPayload | null;
    row_errors?: string[] | null;
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
    const payload = batch.parsed_payload ?? {};
    const preview = payload.preview ?? {};
    const errors = batch.row_errors ?? [];
    const unmatched = payload.unmatched_codes ?? [];

    return (
        <AuthenticatedLayout title="Preview Import CCR 022C">
            <Head title="Preview Import CCR 022C" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions column={{ xs: 1, sm: 2 }}>
                    <Descriptions.Item label="File">{batch.original_filename}</Descriptions.Item>
                    <Descriptions.Item label="Tanggal Produksi">{payload.production_date ?? '—'}</Descriptions.Item>
                    <Descriptions.Item label="Status">
                        <Tag color={STATUS_COLOR[batch.status] ?? 'default'}>{batch.status}</Tag>
                    </Descriptions.Item>
                    <Descriptions.Item label="Total Trip">{preview.trip_count ?? 0}</Descriptions.Item>
                    <Descriptions.Item label="Rollup OB (BCM)">{preview.ob_bcm?.toLocaleString('id-ID') ?? 0}</Descriptions.Item>
                    <Descriptions.Item label="Rollup Coal (Mton)">{preview.coal_ton?.toLocaleString('id-ID') ?? 0}</Descriptions.Item>
                </Descriptions>
            </Card>

            {batch.status === 'parsing' && (
                <Alert type="info" message="File sedang diproses. Refresh halaman untuk melihat hasil." showIcon />
            )}

            {unmatched.length > 0 && (
                <Card title={`Kode unit belum match (${unmatched.length})`} style={{ marginBottom: 16 }}>
                    <List
                        size="small"
                        dataSource={unmatched}
                        renderItem={(code) => <List.Item>{code}</List.Item>}
                    />
                </Card>
            )}

            {errors.length > 0 && (
                <Card title={`Error parsing (${errors.length})`} style={{ marginBottom: 16 }}>
                    <List size="small" dataSource={errors.slice(0, 20)} renderItem={(msg) => <List.Item>{msg}</List.Item>} />
                    {errors.length > 20 && <Alert type="warning" message={`...dan ${errors.length - 20} error lainnya`} />}
                </Card>
            )}

            {batch.status === 'preview' && (
                <Button type="primary" onClick={() => router.post(route('ccr-022c.import.confirm', batch.id))}>
                    Import & Rollup
                </Button>
            )}
        </AuthenticatedLayout>
    );
}
