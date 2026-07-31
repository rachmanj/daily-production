import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { Site } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Button, Card, DatePicker, Form, InputNumber, Select, Space } from 'antd';
import { dayjs } from '@/lib/date';

interface EquipmentOption {
    equipment_id: number;
    unit_code: string;
}

interface TripEntryProps {
    site: Site;
    shifts: Array<{ id: number; name: string }>;
    materials: Record<string, string>;
    excavators: EquipmentOption[];
    haulers: EquipmentOption[];
}

export default function TripEntry({ site, shifts, materials, excavators, haulers }: TripEntryProps) {
    const { data, setData, post, processing } = useForm({
        site_id: site.id,
        production_date: dayjs().format('YYYY-MM-DD'),
        shift_id: shifts[0]?.id ?? 1,
        excavator_id: excavators[0]?.equipment_id ?? 0,
        hauler_id: haulers[0]?.equipment_id ?? 0,
        material_type: Object.keys(materials)[0] ?? 'ob',
        hour_slot: dayjs().hour(),
        volume_bcm: 0,
        load_percent: 100,
        trip_count: 1,
        truck_capacity_bcm: 0,
    });

    const submit = () => {
        post(route('ccr-022c.trip-entry.store'));
    };

    return (
        <AuthenticatedLayout title="Tambah Trip CCR 022C">
            <Head title="Tambah Trip" />
            <Card style={{ maxWidth: 480, margin: '0 auto' }}>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Tanggal" required>
                        <DatePicker
                            style={{ width: '100%' }}
                            value={dayjs(data.production_date)}
                            onChange={(d) => setData('production_date', d?.format('YYYY-MM-DD') ?? data.production_date)}
                            size="large"
                        />
                    </Form.Item>
                    <Form.Item label="Shift" required>
                        <Select
                            size="large"
                            value={data.shift_id}
                            onChange={(v) => setData('shift_id', v)}
                            options={shifts.map((s) => ({ value: s.id, label: s.name }))}
                        />
                    </Form.Item>
                    <Form.Item label="Excavator" required>
                        <Select
                            size="large"
                            showSearch
                            optionFilterProp="label"
                            value={data.excavator_id}
                            onChange={(v) => setData('excavator_id', v)}
                            options={excavators.map((e) => ({ value: e.equipment_id, label: e.unit_code }))}
                        />
                    </Form.Item>
                    <Form.Item label="Hauler" required>
                        <Select
                            size="large"
                            showSearch
                            optionFilterProp="label"
                            value={data.hauler_id}
                            onChange={(v) => setData('hauler_id', v)}
                            options={haulers.map((h) => ({ value: h.equipment_id, label: h.unit_code }))}
                        />
                    </Form.Item>
                    <Form.Item label="Material" required>
                        <Select
                            size="large"
                            value={data.material_type}
                            onChange={(v) => setData('material_type', v)}
                            options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                        />
                    </Form.Item>
                    <Form.Item label="Jam (0-23)" required>
                        <InputNumber
                            size="large"
                            style={{ width: '100%' }}
                            min={0}
                            max={23}
                            value={data.hour_slot}
                            onChange={(v) => setData('hour_slot', v ?? 0)}
                        />
                    </Form.Item>
                    <Form.Item label="Volume (BCM)" required>
                        <InputNumber
                            size="large"
                            style={{ width: '100%' }}
                            min={0}
                            step={0.1}
                            value={data.volume_bcm}
                            onChange={(v) => setData('volume_bcm', v ?? 0)}
                        />
                    </Form.Item>
                    <Space style={{ width: '100%' }} size="middle">
                        <Form.Item label="% Load" style={{ flex: 1 }}>
                            <InputNumber
                                size="large"
                                min={0}
                                max={200}
                                value={data.load_percent}
                                onChange={(v) => setData('load_percent', v ?? 100)}
                            />
                        </Form.Item>
                        <Form.Item label="Ret/Trip" style={{ flex: 1 }}>
                            <InputNumber
                                size="large"
                                min={0.1}
                                step={0.1}
                                value={data.trip_count}
                                onChange={(v) => setData('trip_count', v ?? 1)}
                            />
                        </Form.Item>
                    </Space>
                    <Button type="primary" htmlType="submit" loading={processing} block size="large">
                        Simpan Trip
                    </Button>
                </Form>
            </Card>
        </AuthenticatedLayout>
    );
}
