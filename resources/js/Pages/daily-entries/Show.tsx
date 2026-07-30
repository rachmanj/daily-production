import EntryTabs from '@/Components/entry/EntryTabs';
import StatusBadge from '@/Components/entry/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatDate } from '@/lib/date';
import type {
    DailyEntry,
    EquipmentAssignment,
    FuelTypeOption,
    HourlyTotal,
    PitOption,
    ShiftOption,
} from '@/types/daily-entry';
import { Head, Link, router } from '@inertiajs/react';
import { Button, Card, Descriptions, Space, Tag } from 'antd';
import { EditOutlined } from '@ant-design/icons';

interface ShowProps {
    entry: DailyEntry;
    pits: PitOption[];
    shifts: ShiftOption[];
    fuelTypes: FuelTypeOption[];
    equipmentAssignments: EquipmentAssignment[];
    productionActivities: Record<string, string>;
    fuelCategories: Record<string, string>;
    ccrEnabled?: boolean;
    hourlyTotals?: HourlyTotal[] | null;
}

export default function Show({
    entry,
    pits,
    shifts,
    fuelTypes,
    equipmentAssignments,
    productionActivities,
    fuelCategories,
    ccrEnabled,
    hourlyTotals,
}: ShowProps) {
    return (
        <AuthenticatedLayout title={`Entry — ${formatDate(entry.production_date)}`}>
            <Head title="Detail Entry" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions size="small" column={{ xs: 1, sm: 2, md: 4 }}>
                    <Descriptions.Item label="Site">
                        {entry.site?.code} — {entry.site?.name}
                    </Descriptions.Item>
                    <Descriptions.Item label="Tanggal">
                        {formatDate(entry.production_date)}
                    </Descriptions.Item>
                    <Descriptions.Item label="Status">
                        <StatusBadge status={entry.status} />
                    </Descriptions.Item>
                    <Descriptions.Item label="Dibuat Oleh">{entry.creator?.name}</Descriptions.Item>
                    {entry.approver && (
                        <Descriptions.Item label="Disetujui Oleh">{entry.approver.name}</Descriptions.Item>
                    )}
                    {ccrEnabled && hourlyTotals && hourlyTotals.length > 0 && (
                        <Descriptions.Item label="Total Hourly (CCR)">
                            <Space wrap size={[4, 4]}>
                                {hourlyTotals
                                    .filter((t) => t.total_tonnage > 0)
                                    .map((t) => (
                                        <Tag key={t.material_type}>
                                            {t.material_label}:{' '}
                                            {t.total_tonnage.toLocaleString('id-ID', { maximumFractionDigits: 0 })}{' '}
                                            ton
                                        </Tag>
                                    ))}
                            </Space>
                        </Descriptions.Item>
                    )}
                </Descriptions>
                <Space style={{ marginTop: 16 }}>
                    {entry.status === 'draft' && (
                        <Link href={route('daily-entries.edit', entry.id)}>
                            <Button icon={<EditOutlined />}>Edit</Button>
                        </Link>
                    )}
                    {entry.status === 'draft' && (
                        <Button
                            type="primary"
                            onClick={() => router.post(route('daily-entries.submit', entry.id))}
                        >
                            Submit
                        </Button>
                    )}
                    {entry.status === 'submitted' && (
                        <>
                            <Button
                                type="primary"
                                onClick={() => router.post(route('daily-entries.approve', entry.id))}
                            >
                                Approve
                            </Button>
                            <Button danger onClick={() => router.post(route('daily-entries.reject', entry.id))}>
                                Reject
                            </Button>
                        </>
                    )}
                </Space>
            </Card>
            <EntryTabs
                entry={entry}
                pits={pits}
                shifts={shifts}
                fuelTypes={fuelTypes}
                equipmentAssignments={equipmentAssignments}
                productionActivities={productionActivities}
                fuelCategories={fuelCategories}
                projectCode={entry.site?.code}
                readOnly
                ccrEnabled={ccrEnabled}
                hourlyTotals={hourlyTotals}
            />
        </AuthenticatedLayout>
    );
}
