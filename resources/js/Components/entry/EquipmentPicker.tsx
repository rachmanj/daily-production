import type { EquipmentSearchResult } from '@/types/daily-entry';
import { Select, Spin } from 'antd';
import axios from 'axios';
import { useCallback, useEffect, useMemo, useState } from 'react';

interface EquipmentPickerProps {
    value?: number;
    onChange?: (equipmentId: number | undefined, unit?: EquipmentSearchResult) => void;
    projectCode?: string;
    placeholder?: string;
    disabled?: boolean;
    style?: React.CSSProperties;
}

export default function EquipmentPicker({
    value,
    onChange,
    projectCode,
    placeholder = 'Cari equipment...',
    disabled,
    style,
}: EquipmentPickerProps) {
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(false);
    const [options, setOptions] = useState<EquipmentSearchResult[]>([]);
    const [selected, setSelected] = useState<EquipmentSearchResult | null>(null);

    const fetchEquipment = useCallback(async (query: string) => {
        setLoading(true);
        try {
            const response = await axios.get(route('equipment-assignments.search'), {
                params: {
                    search: query || undefined,
                    project_code: projectCode,
                    is_active: 1,
                },
                withCredentials: true,
            });
            setOptions(response.data.data ?? []);
        } catch {
            setOptions([]);
        } finally {
            setLoading(false);
        }
    }, [projectCode]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchEquipment(search);
        }, 400);

        return () => clearTimeout(timer);
    }, [search, fetchEquipment]);

    useEffect(() => {
        if (value && !selected) {
            const found = options.find((o) => o.id === value);
            if (found) {
                setSelected(found);
            }
        }
    }, [value, options, selected]);

    const selectOptions = useMemo(() => {
        const merged = [...options];
        if (selected && !merged.find((o) => o.id === selected.id)) {
            merged.unshift(selected);
        }
        return merged.map((item) => ({
            value: item.id,
            label: `${item.unit_code}${item.description ? ` — ${item.description}` : ''}`,
            item,
        }));
    }, [options, selected]);

    return (
        <Select
            showSearch
            allowClear
            filterOption={false}
            value={value}
            placeholder={placeholder}
            disabled={disabled}
            style={{ width: '100%', ...style }}
            notFoundContent={loading ? <Spin size="small" /> : 'Tidak ditemukan'}
            onSearch={setSearch}
            onChange={(id, option) => {
                const item = (option as { item?: EquipmentSearchResult })?.item ?? null;
                setSelected(item);
                onChange?.(id, item ?? undefined);
            }}
            options={selectOptions}
        />
    );
}
