import HourlyGrid from '@/Components/hourly/HourlyGrid';
import StatusBadge from '@/Components/entry/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDate } from '@/lib/date';
import type { EquipmentColumn, HourlyRecord } from '@/types/hourly';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Descriptions, Select, Space, message } from 'antd';
import axios from 'axios';
import { useState } from 'react';

interface DailyEntryData {
    id: number;
    production_date: string;
    status: string;
    site?: { id: number; code: string; name: string } | null;
}

interface ShiftOption {
    id: number;
    name: string;
}

interface HourlyEditProps {
    entry: DailyEntryData;
    materialType: string;
    materialLabel: string;
    shiftId: number;
    shifts: ShiftOption[];
    equipment: EquipmentColumn[];
    records: HourlyRecord[];
    hourlyTarget: number | null;
    materials: Record<string, string>;
}

export default function Edit({
    entry,
    materialType,
    materialLabel,
    shiftId,
    shifts,
    equipment,
    records: initialRecords,
    hourlyTarget,
    materials,
}: HourlyEditProps) {
    const [records, setRecords] = useState<HourlyRecord[]>(initialRecords);
    const [saving, setSaving] = useState(false);
    const readOnly = entry.status !== 'draft';

    const save = async () => {
        setSaving(true);
        try {
            await axios.put(route('hourly.update', entry.id), {
                material_type: materialType,
                shift_id: shiftId,
                records,
            });
            message.success('Data hourly disimpan');
            router.reload({ only: ['records'] });
        } catch {
            message.error('Gagal menyimpan data hourly');
        } finally {
            setSaving(false);
        }
    };

    const changeMaterial = (value: string) => {
        router.get(route('hourly.edit', entry.id), { material_type: value, shift_id: shiftId });
    };

    const changeShift = (value: number) => {
        router.get(route('hourly.edit', entry.id), { material_type: materialType, shift_id: value });
    };

    return (
        <AuthenticatedLayout title={`Hourly Entry — ${formatDate(entry.production_date)}`}>
            <Head title="Edit CCR Hourly" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions size="small" column={{ xs: 1, sm: 2, md: 4 }}>
                    <Descriptions.Item label="Site">
                        {entry.site?.code} — {entry.site?.name}
                    </Descriptions.Item>
                    <Descriptions.Item label="Tanggal">{formatDate(entry.production_date)}</Descriptions.Item>
                    <Descriptions.Item label="Material">
                        <Select
                            size="small"
                            value={materialType}
                            disabled={readOnly}
                            onChange={changeMaterial}
                            options={Object.entries(materials).map(([value, label]) => ({ value, label }))}
                            style={{ minWidth: 160 }}
                        />
                    </Descriptions.Item>
                    <Descriptions.Item label="Shift">
                        <Select
                            size="small"
                            value={shiftId}
                            disabled={readOnly}
                            onChange={changeShift}
                            options={shifts.map((s) => ({ value: s.id, label: s.name }))}
                            style={{ minWidth: 100 }}
                        />
                    </Descriptions.Item>
                    <Descriptions.Item label="Status">
                        <StatusBadge status={entry.status} />
                    </Descriptions.Item>
                    <Descriptions.Item label="Target/jam">
                        {hourlyTarget ? `${hourlyTarget.toLocaleString('id-ID')} Mton` : '—'}
                    </Descriptions.Item>
                    <Descriptions.Item label="Aksi">
                        <Space>
                            {!readOnly && (
                                <Button type="primary" onClick={save} loading={saving}>
                                    Simpan
                                </Button>
                            )}
                            {entry.status === 'draft' && (
                                <Button onClick={() => router.post(route('daily-entries.submit', entry.id))}>
                                    Submit
                                </Button>
                            )}
                        </Space>
                    </Descriptions.Item>
                </Descriptions>
            </Card>
            <Card title={`Grid Produksi — ${materialLabel}`}>
                {equipment.length === 0 ? (
                    <p>Belum ada alat ter-assign untuk material ini. Atur di menu Equipment Assignment.</p>
                ) : (
                    <HourlyGrid
                        equipment={equipment}
                        records={records}
                        shiftId={shiftId}
                        hourlyTarget={hourlyTarget}
                        readOnly={readOnly}
                        onChange={setRecords}
                    />
                )}
            </Card>
        </AuthenticatedLayout>
    );
}
