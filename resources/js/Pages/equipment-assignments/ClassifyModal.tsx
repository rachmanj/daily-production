import { router } from '@inertiajs/react';
import { Form, Input, InputNumber, Modal, Select, Switch, message } from 'antd';
import { useEffect } from 'react';

interface AssignmentRow {
    id: number;
    unit_code: string;
    material_type?: string | null;
    equipment_role?: string | null;
    display_order?: number | null;
    is_active_for_tracking: boolean;
}

interface ClassifyModalProps {
    open: boolean;
    assignment: AssignmentRow | null;
    materialOptions: Record<string, string>;
    onClose: () => void;
}

interface ClassifyFormValues {
    material_type: string;
    equipment_role: string | null;
    display_order: number | null;
    is_active_for_tracking: boolean;
}

export default function ClassifyModal({ open, assignment, materialOptions, onClose }: ClassifyModalProps) {
    const [form] = Form.useForm<ClassifyFormValues>();

    useEffect(() => {
        if (open && assignment) {
            form.setFieldsValue({
                material_type: assignment.material_type ?? '',
                equipment_role: assignment.equipment_role ?? null,
                display_order: assignment.display_order ?? null,
                is_active_for_tracking: assignment.is_active_for_tracking,
            });
        }
    }, [open, assignment, form]);

    const handleSubmit = () => {
        if (!assignment) {
            return;
        }

        form.validateFields().then((values) => {
            router.put(
                route('equipment-assignments.update', assignment.id),
                {
                    material_type: values.material_type || null,
                    equipment_role: values.equipment_role || null,
                    display_order: values.display_order ?? null,
                    is_active_for_tracking: values.is_active_for_tracking,
                },
                {
                    onSuccess: () => {
                        message.success('Klasifikasi CCR berhasil disimpan.');
                        onClose();
                    },
                    onError: () => message.error('Gagal menyimpan klasifikasi CCR.'),
                },
            );
        });
    };

    return (
        <Modal
            title={assignment ? `Klasifikasi CCR — ${assignment.unit_code}` : 'Klasifikasi CCR'}
            open={open}
            onCancel={onClose}
            onOk={handleSubmit}
            okText="Simpan"
            cancelText="Batal"
            destroyOnClose
        >
            <Form form={form} layout="vertical">
                <Form.Item label="Material Type" name="material_type">
                    <Select
                        allowClear
                        placeholder="Umum (semua material)"
                        options={[
                            { value: '', label: 'Umum (semua material)' },
                            ...Object.entries(materialOptions).map(([value, label]) => ({ value, label })),
                        ]}
                    />
                </Form.Item>
                <Form.Item label="Equipment Role" name="equipment_role">
                    <Input placeholder="contoh: loader" allowClear />
                </Form.Item>
                <Form.Item label="Display Order" name="display_order">
                    <InputNumber min={0} style={{ width: '100%' }} placeholder="Urutan kolom di grid hourly" />
                </Form.Item>
                <Form.Item label="Tracking Aktif" name="is_active_for_tracking" valuePropName="checked">
                    <Switch />
                </Form.Item>
            </Form>
        </Modal>
    );
}
