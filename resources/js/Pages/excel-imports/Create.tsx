import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Form, Select, Upload, message } from 'antd';
import { InboxOutlined } from '@ant-design/icons';
import { useState } from 'react';

const { Dragger } = Upload;

export default function Create() {
    const [type, setType] = useState('dpr');
    const [fileList, setFileList] = useState<File[]>([]);
    const [uploading, setUploading] = useState(false);

    const submit = () => {
        if (fileList.length === 0) {
            message.warning('Pilih file Excel terlebih dahulu.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileList[0]);
        formData.append('type', type);

        setUploading(true);
        router.post(route('excel-imports.store'), formData, {
            forceFormData: true,
            onFinish: () => setUploading(false),
        });
    };

    return (
        <AuthenticatedLayout title="Import Excel">
            <Head title="Import Excel" />
            <Card style={{ maxWidth: 600 }}>
                <Form layout="vertical">
                    <Form.Item label="Tipe Import">
                        <Select
                            value={type}
                            onChange={setType}
                            options={[
                                { value: 'dpr', label: 'DPR (Daily Production Report)' },
                                { value: 'fuel', label: 'Fuel Report' },
                                { value: 'site_info', label: 'Daily Info Site' },
                            ]}
                        />
                    </Form.Item>
                    <Form.Item label="File Excel">
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
                            <p className="ant-upload-text">Klik atau drag file Excel ke sini</p>
                            <p className="ant-upload-hint">Format .xlsx atau .xls</p>
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
