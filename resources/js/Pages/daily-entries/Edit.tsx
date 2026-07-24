import EntryTabs from '@/Components/entry/EntryTabs';
import EntryWizard from '@/Components/entry/EntryWizard';
import StatusBadge from '@/Components/entry/StatusBadge';
import SyncButton from '@/Components/offline/SyncButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type {
    DailyEntry,
    EquipmentAssignment,
    FuelTypeOption,
    PitOption,
    ShiftOption,
} from '@/types/daily-entry';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Descriptions, Space } from 'antd';
import dayjs from 'dayjs';
import { useEffect, useState } from 'react';

interface EditProps {
    entry: DailyEntry;
    pits: PitOption[];
    shifts: ShiftOption[];
    fuelTypes: FuelTypeOption[];
    equipmentAssignments: EquipmentAssignment[];
    productionActivities: Record<string, string>;
    fuelCategories: Record<string, string>;
}

export default function Edit({
    entry,
    pits,
    shifts,
    fuelTypes,
    equipmentAssignments,
    productionActivities,
    fuelCategories,
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
        <AuthenticatedLayout title={`Edit Entry — ${dayjs(entry.production_date).format('DD MMM YYYY')}`}>
            <Head title="Edit Entry" />
            <Card style={{ marginBottom: 16 }}>
                <Descriptions size="small" column={{ xs: 1, sm: 2, md: 4 }}>
                    <Descriptions.Item label="Site">
                        {entry.site?.code} — {entry.site?.name}
                    </Descriptions.Item>
                    <Descriptions.Item label="Tanggal">
                        {dayjs(entry.production_date).format('DD MMM YYYY')}
                    </Descriptions.Item>
                    <Descriptions.Item label="Status">
                        <StatusBadge status={entry.status} />
                    </Descriptions.Item>
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
                />
            )}
        </AuthenticatedLayout>
    );
}
