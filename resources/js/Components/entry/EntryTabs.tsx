import DeploymentForm, { type DeploymentRow } from '@/Components/entry/DeploymentForm';
import FuelForm, { type FuelRow } from '@/Components/entry/FuelForm';
import HourlySummaryTab from '@/Components/entry/HourlySummaryTab';
import ProductionForm, { type ProductionRow } from '@/Components/entry/ProductionForm';
import SiteInfoForm from '@/Components/entry/SiteInfoForm';
import type {
    DailyEntry,
    EquipmentAssignment,
    FuelTypeOption,
    HourlyTotal,
    PitOption,
    ShiftOption,
    SiteInfo,
} from '@/types/daily-entry';
import { router } from '@inertiajs/react';
import { Button, Space, Tabs, message } from 'antd';
import { useState } from 'react';

interface EntryTabsProps {
    entry: DailyEntry;
    pits: PitOption[];
    shifts: ShiftOption[];
    fuelTypes: FuelTypeOption[];
    equipmentAssignments: EquipmentAssignment[];
    productionActivities: Record<string, string>;
    fuelCategories: Record<string, string>;
    projectCode?: string;
    readOnly?: boolean;
    ccrEnabled?: boolean;
    hourlyTotals?: HourlyTotal[] | null;
}

export default function EntryTabs({
    entry,
    pits,
    shifts,
    fuelTypes,
    equipmentAssignments,
    productionActivities,
    fuelCategories,
    projectCode,
    readOnly,
    ccrEnabled,
    hourlyTotals,
}: EntryTabsProps) {
    const [production, setProduction] = useState<ProductionRow[]>([]);
    const [fuel, setFuel] = useState<FuelRow[]>([]);
    const [deployments, setDeployments] = useState<DeploymentRow[]>([]);
    const [siteInfo, setSiteInfo] = useState<Partial<SiteInfo>>(entry.site_info ?? {});
    const [saving, setSaving] = useState<string | null>(null);

    const saveProduction = () => {
        setSaving('production');
        router.put(
            route('production-records.update', entry.id),
            { records: production.length ? production : entry.production_records ?? [] } as never,
            {
                preserveScroll: true,
                onSuccess: () => message.success('Data produksi disimpan.'),
                onFinish: () => setSaving(null),
            },
        );
    };

    const saveFuel = () => {
        setSaving('fuel');
        router.put(
            route('fuel-records.update', entry.id),
            { records: fuel.length ? fuel : entry.fuel_records ?? [] } as never,
            {
                preserveScroll: true,
                onSuccess: () => message.success('Data fuel disimpan.'),
                onFinish: () => setSaving(null),
            },
        );
    };

    const saveDeployment = () => {
        setSaving('deployment');
        router.put(
            route('equipment-deployments.update', entry.id),
            { records: deployments.length ? deployments : entry.equipment_deployments ?? [] } as never,
            {
                preserveScroll: true,
                onSuccess: () => message.success('Data deployment disimpan.'),
                onFinish: () => setSaving(null),
            },
        );
    };

    const saveSiteInfo = () => {
        setSaving('site-info');
        router.put(route('site-info.update', entry.id), siteInfo as never, {
            preserveScroll: true,
            onSuccess: () => message.success('Info site disimpan.'),
            onFinish: () => setSaving(null),
        });
    };

    const items = [
        {
            key: 'production',
            label: 'Produksi',
            children: (
                <>
                    <ProductionForm
                        pits={pits}
                        shifts={shifts}
                        records={entry.production_records ?? []}
                        productionActivities={productionActivities}
                        onChange={setProduction}
                        disabled={readOnly}
                    />
                    {!readOnly && (
                        <Space style={{ marginTop: 16 }}>
                            <Button type="primary" loading={saving === 'production'} onClick={saveProduction}>
                                Simpan Produksi
                            </Button>
                        </Space>
                    )}
                </>
            ),
        },
        {
            key: 'fuel',
            label: 'Fuel',
            children: (
                <>
                    <FuelForm
                        records={entry.fuel_records ?? []}
                        shifts={shifts}
                        fuelTypes={fuelTypes}
                        fuelCategories={fuelCategories}
                        equipmentAssignments={equipmentAssignments}
                        projectCode={projectCode}
                        onChange={setFuel}
                        disabled={readOnly}
                    />
                    {!readOnly && (
                        <Space style={{ marginTop: 16 }}>
                            <Button type="primary" loading={saving === 'fuel'} onClick={saveFuel}>
                                Simpan Fuel
                            </Button>
                        </Space>
                    )}
                </>
            ),
        },
        {
            key: 'deployment',
            label: 'Deployment',
            children: (
                <>
                    <DeploymentForm
                        records={entry.equipment_deployments ?? []}
                        pits={pits}
                        shifts={shifts}
                        equipmentAssignments={equipmentAssignments}
                        onChange={setDeployments}
                        disabled={readOnly}
                    />
                    {!readOnly && (
                        <Space style={{ marginTop: 16 }}>
                            <Button type="primary" loading={saving === 'deployment'} onClick={saveDeployment}>
                                Simpan Deployment
                            </Button>
                        </Space>
                    )}
                </>
            ),
        },
        {
            key: 'site-info',
            label: 'Info Site',
            children: (
                <>
                    <SiteInfoForm data={entry.site_info ?? null} onChange={setSiteInfo} disabled={readOnly} />
                    {!readOnly && (
                        <Space style={{ marginTop: 16 }}>
                            <Button type="primary" loading={saving === 'site-info'} onClick={saveSiteInfo}>
                                Simpan Info Site
                            </Button>
                        </Space>
                    )}
                </>
            ),
        },
    ];

    if (ccrEnabled) {
        items.push({
            key: 'ccr-hourly',
            label: 'CCR Hourly',
            children: <HourlySummaryTab totals={hourlyTotals ?? []} entryId={entry.id} />,
        });
    }

    return <Tabs items={items} />;
}
