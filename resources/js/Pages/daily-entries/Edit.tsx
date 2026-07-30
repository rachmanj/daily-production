import EntryTabs from '@/Components/entry/EntryTabs';
import EntryWizard from '@/Components/entry/EntryWizard';
import StatusBadge from '@/Components/entry/StatusBadge';
import SyncButton from '@/Components/offline/SyncButton';
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
import { Head, router } from '@inertiajs/react';
import { Button, Card, Descriptions, Space, Tag } from 'antd';
import { useEffect, useState } from 'react';

interface EditProps {
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

export default function Edit({
    entry,
    pits,
    shifts,
    fuelTypes,
    equipmentAssignments,
    productionActivities,
    fuelCategories,
    ccrEnabled,
    hourlyTotals,
}: EditProps) {
    const [isMobile, setIsMobile] = useState(false);
    const readOnly = entry.status !== 'draft';

    useEffect(() => {
        const check = () => setIsMobile(window.innerWidth < 768);
        check();
        window.addEventListener('resize', check);
        return () => window.removeEventListener('resize', check);
    }, []);

    return (
        <AuthenticatedLayout title={`Edit Entry — ${formatDate(entry.production_date)}`}>
            <Head title="Edit Entry" />
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
                    <Descriptions.Item label="Aksi">
                        <Space>
                            <SyncButton />
                            {entry.status === 'draft' && (
                                <Button
                                    type="primary"
                                    onClick={() => router.post(route('daily-entries.submit', entry.id))}
                                >
                                    Submit
                                </Button>
                            )}
                        </Space>
                    </Descriptions.Item>
                </Descriptions>
            </Card>
            {isMobile ? (
                <EntryWizard
                    entry={entry}
                    pits={pits}
                    shifts={shifts}
                    fuelTypes={fuelTypes}
                    equipmentAssignments={equipmentAssignments}
                    productionActivities={productionActivities}
                    fuelCategories={fuelCategories}
                    projectCode={entry.site?.code}
                    ccrEnabled={ccrEnabled}
                    hourlyTotals={hourlyTotals}
                />
            ) : (
                <EntryTabs
                    entry={entry}
                    pits={pits}
                    shifts={shifts}
                    fuelTypes={fuelTypes}
                    equipmentAssignments={equipmentAssignments}
                    productionActivities={productionActivities}
                    fuelCategories={fuelCategories}
                    projectCode={entry.site?.code}
                    readOnly={readOnly}
                    ccrEnabled={ccrEnabled}
                    hourlyTotals={hourlyTotals}
                />
            )}
        </AuthenticatedLayout>
    );
}
