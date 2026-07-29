export interface EquipmentColumn {
    assignment_id: number;
    equipment_id: number;
    unit_code: string;
    equipment_role?: string | null;
    display_order?: number | null;
    plant_type_name?: string | null;
}

export interface HourlyRecord {
    id?: number;
    equipment_id: number;
    unit_code?: string;
    shift_id?: number;
    hour_slot: number;
    tonnage: number;
    location?: string | null;
    loader_info?: string | null;
}

export interface HourlyKpiData {
    dtd: { actual: number; plan: number | null; achievement: number | null };
    mtd: { actual: number; plan: number | null; achievement: number | null };
    current_hour: {
        hour_slot: number;
        tonnage: number;
        target: number | null;
        achievement: number | null;
    } | null;
    hourly_target: number | null;
}

export interface HeatmapData {
    equipment: EquipmentColumn[];
    hourly_target: number | null;
    grid: Record<number, Record<number, number>>;
}

export function formatHourSlot(slot: number): string {
    const next = (slot + 1) % 24;
    return `${String(slot).padStart(2, '0')}:00–${String(next).padStart(2, '0')}:00`;
}

export function getCellColor(tonnage: number, target: number | null): string {
    if (!target || target <= 0) {
        return 'transparent';
    }
    const ratio = tonnage / target;
    if (ratio >= 0.95) {
        return '#b7eb8f';
    }
    if (ratio >= 0.7) {
        return '#fff566';
    }
    return '#ffa39e';
}

export function getAchievementColor(achievement: number | null): 'success' | 'warning' | 'danger' | undefined {
    if (achievement === null) {
        return undefined;
    }
    if (achievement >= 100) {
        return 'success';
    }
    if (achievement >= 80) {
        return 'warning';
    }
    return 'danger';
}
