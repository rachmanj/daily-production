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
import { Button, Steps, message } from 'antd';
import { useState } from 'react';

interface EntryWizardProps {
    entry: DailyEntry;
    pits: PitOption[];
    shifts: ShiftOption[];
    fuelTypes: FuelTypeOption[];
    equipmentAssignments: EquipmentAssignment[];
    productionActivities: Record<string, string>;
    fuelCategories: Record<string, string>;
    projectCode?: string;
    ccrEnabled?: boolean;
    hourlyTotals?: HourlyTotal[] | null;
}

const BASE_STEPS = [
    { title: 'Produksi', key: 'production' },
    { title: 'Fuel', key: 'fuel' },
    { title: 'Deployment', key: 'deployment' },
    { title: 'Info Site', key: 'site-info' },
];

export default function EntryWizard({
    entry,
    pits,
    shifts,
    fuelTypes,
    equipmentAssignments,
    productionActivities,
    fuelCategories,
    projectCode,
    ccrEnabled,
    hourlyTotals,
}: EntryWizardProps) {
    const steps = ccrEnabled
        ? [...BASE_STEPS, { title: 'CCR Hourly', key: 'ccr-hourly' }]
        : BASE_STEPS;
    const [current, setCurrent] = useState(0);
    const [production, setProduction] = useState<ProductionRow[]>([]);
    const [fuel, setFuel] = useState<FuelRow[]>([]);
    const [deployments, setDeployments] = useState<DeploymentRow[]>([]);
    const [siteInfo, setSiteInfo] = useState<Partial<SiteInfo>>(entry.site_info ?? {});
    const [saving, setSaving] = useState(false);

    const saveCurrentStep = () => {
        const stepKey = steps[current].key;
        if (stepKey === 'ccr-hourly') {
            if (current < steps.length - 1) {
                setCurrent(current + 1);
            }
            return;
        }

        setSaving(true);

        const handlers: Record<string, { url: string; data: object }> = {
            production: {
                url: route('production-records.update', entry.id),
                data: { records: production.length ? production : entry.production_records ?? [] },
            },
            fuel: {
                url: route('fuel-records.update', entry.id),
                data: { records: fuel.length ? fuel : entry.fuel_records ?? [] },
            },
            deployment: {
                url: route('equipment-deployments.update', entry.id),
                data: { records: deployments.length ? deployments : entry.equipment_deployments ?? [] },
            },
            'site-info': {
                url: route('site-info.update', entry.id),
                data: siteInfo,
            },
        };

        const handler = handlers[stepKey];
        router.put(handler.url, handler.data as never, {
            preserveScroll: true,
            onSuccess: () => {
                message.success('Data berhasil disimpan.');
                if (current < steps.length - 1) {
                    setCurrent(current + 1);
                }
            },
            onFinish: () => setSaving(false),
        });
    };

    const stepContent = () => {
        const stepKey = steps[current].key;

        switch (stepKey) {
            case 'production':
                return (
                    <ProductionForm
                        pits={pits}
                        shifts={shifts}
                        records={entry.production_records ?? []}
                        productionActivities={productionActivities}
                        onChange={setProduction}
                    />
                );
            case 'fuel':
                return (
                    <FuelForm
                        records={entry.fuel_records ?? []}
                        shifts={shifts}
                        fuelTypes={fuelTypes}
                        fuelCategories={fuelCategories}
                        equipmentAssignments={equipmentAssignments}
                        projectCode={projectCode}
                        onChange={setFuel}
                    />
                );
            case 'deployment':
                return (
                    <DeploymentForm
                        records={entry.equipment_deployments ?? []}
                        pits={pits}
                        shifts={shifts}
                        equipmentAssignments={equipmentAssignments}
                        onChange={setDeployments}
                    />
                );
            case 'site-info':
                return <SiteInfoForm data={entry.site_info ?? null} onChange={setSiteInfo} />;
            case 'ccr-hourly':
                return <HourlySummaryTab totals={hourlyTotals ?? []} entryId={entry.id} />;
            default:
                return null;
        }
    };

    const isReadOnlyStep = steps[current].key === 'ccr-hourly';

    return (
        <div>
            <Steps current={current} items={steps} size="small" style={{ marginBottom: 24 }} />
            {stepContent()}
            <div style={{ marginTop: 24, display: 'flex', gap: 8 }}>
                {current > 0 && (
                    <Button onClick={() => setCurrent(current - 1)}>Sebelumnya</Button>
                )}
                {!isReadOnlyStep && (
                    <Button type="primary" loading={saving} onClick={saveCurrentStep} style={{ flex: 1 }}>
                        {current < steps.length - 1 ? 'Simpan & Lanjut' : 'Simpan'}
                    </Button>
                )}
                {isReadOnlyStep && current < steps.length - 1 && (
                    <Button type="primary" onClick={() => setCurrent(current + 1)} style={{ flex: 1 }}>
                        Lanjut
                    </Button>
                )}
            </div>
        </div>
    );
}
