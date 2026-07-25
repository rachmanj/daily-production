export interface KpiData {
    ob: { today: number; mtd: number; achievement: number | null };
    coal: { today: number; mtd: number; achievement: number | null };
    stripping_ratio: { mtd: number | null; ytd: number | null };
    fuel: { today_liters: number; mtd_liters: number };
}

export interface TrendPoint {
    date: string;
    ob: number;
    coal: number;
    sr: number | null;
}

export interface PerPitPoint {
    pit_id: number;
    pit_code: string;
    ob: number;
    coal: number;
}

export interface UtilizationData {
    active: number;
    standby: number;
    breakdown: number;
    total: number;
}

export interface FuelByEquipmentRow {
    equipment_id: number;
    unit_code: string;
    liters: number;
    hours: number;
    fcr: number | null;
}

export interface ConsolidatedSiteRow {
    site_id: number;
    site_code: string;
    site_name: string;
    ob: number;
    coal: number;
    hauling: number;
    fuel_liters: number;
    sr: number | null;
}

export interface ConsolidatedSummary {
    totals: {
        ob: number;
        coal: number;
        hauling: number;
        fuel_liters: number;
        sr: number | null;
    };
    sites: ConsolidatedSiteRow[];
    trend: TrendPoint[];
    date_from: string;
    date_to: string;
}
