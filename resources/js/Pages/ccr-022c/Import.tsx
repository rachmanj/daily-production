import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Form, Select, Upload, message } from 'antd';
import { InboxOutlined } from '@ant-design/icons';
import { useState } from 'react';

const { Dragger } = Upload;

interface ImportProps {
    sites: Site[];
}

export default function Import({ sites }: ImportProps) {
    const [siteId, setSiteId] = useState(sites[0]?.id ?? 0);
    const [fileList, setFileList] = useState<File[]>([]);
    const [uploading, setUploading] = useState(false);

    const submit = () => {
        if (fileList.length === 0) {
            message.warning('Pilih file CCR 022C terlebih dahulu.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileList[0]);
        formData.append('site_id', String(siteId));

        setUploading(true);
        router.post(route('ccr-022c.import.store'), formData, {
            forceFormData: true,
            onFinish: () => setUploading(false),
        });
    };

    return (
        <AuthenticatedLayout title="Import CCR 022C">
            <Head title="Import CCR 022C" />
            <Card style={{ maxWidth: 640 }}>
                <Form layout="vertical">
                    <Form.Item label="Site">
                        <Select
                            value={siteId}
                            onChange={setSiteId}
                            options={sites.map((s) => ({ value: s.id, label: `${s.code} — ${s.name}` }))}
                        />
                    </Form.Item>
                    <Form.Item label="File Excel (sheet DATA TRIP)">
                        <Dragger
                            accept=".xlsx,.xls"
                            maxCount={1}
                            beforeUpload={(file) => {
                                setFileList([file]);
                                return false;
                            }}
                            onRemove={() => setFileList([])}
                            fileList={fileList.map((f, i) => ({
                                uid: String(i),
                                name: f.name,
                                status: 'done' as const,
                            }))}
                        >
                            <p className="ant-upload-drag-icon">
                                <InboxOutlined />
                            </p>
                            <p className="ant-upload-text">Klik atau drag file CCR 022C ke sini</p>
                            <p className="ant-upload-hint">Sheet DATA TRIP akan di-parse otomatis</p>
                        </Dragger>
                    </Form.Item>
                    <Button type="primary" loading={uploading} onClick={submit} block>
                        Upload & Preview
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
