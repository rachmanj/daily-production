export type EntryStatus = 'draft' | 'submitted' | 'approved';

export interface DailyEntry {
    id: number;
    uuid: string;
    production_date: string;
    site_id: number;
    status: EntryStatus;
    source?: string;
    submitted_at?: string | null;
    approved_at?: string | null;
    site?: { id: number; code: string; name: string };
    creator?: { id: number; name: string };
    approver?: { id: number; name: string } | null;
    production_records?: ProductionRecord[];
    fuel_records?: FuelRecord[];
    equipment_deployments?: EquipmentDeployment[];
    site_info?: SiteInfo | null;
}

export interface ProductionRecord {
    id?: number;
    pit_id: number;
    shift_id: number;
    ob_removal_bcm?: number | null;
    coal_getting_ton?: number | null;
    coal_hauling_ton?: number | null;
    activity?: string | null;
    truck_count?: number | null;
    pit?: { id: number; code: string };
    shift?: { id: number; name: string };
}

export interface FuelRecord {
    id?: number;
    equipment_id: number;
    unit_code: string;
    shift_id: number;
    fuel_type_id?: number | null;
    liters: number;
    working_hours?: number | null;
    usage_category: string;
    shift?: { id: number; name: string };
    fuel_type?: { id: number; name: string };
}

export interface EquipmentDeployment {
    id?: number;
    equipment_id: number;
    unit_code: string;
    pit_id?: number | null;
    shift_id: number;
    prod_ob_bcm?: number | null;
    prod_coal_ton?: number | null;
    operator_name?: string | null;
    pit?: { id: number; code: string };
    shift?: { id: number; name: string };
}

export interface SiteInfo {
    id?: number;
    weather?: string | null;
    rain_hours?: number | null;
    slippery_hours?: number | null;
    manpower_plan?: number | null;
    manpower_actual?: number | null;
    safety_notes?: string | null;
    fuel_stock_liters?: number | null;
}

export interface EquipmentAssignment {
    id: number;
    equipment_id: number;
    unit_code: string;
    description?: string;
    plant_type_name?: string;
    project_code?: string;
    pit_id?: number;
}

export interface EquipmentSearchResult {
    id: number;
    unit_code: string;
    description?: string;
    plant_type_name?: string;
    project_code: string;
}

export interface ShiftOption {
    id: number;
    name: string;
    site_id: number;
}

export interface PitOption {
    id: number;
    code: string;
    site_id: number;
    owner?: string;
}

export interface FuelTypeOption {
    id: number;
    name: string;
}
