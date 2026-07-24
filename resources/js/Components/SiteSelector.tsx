import { Select } from 'antd';
import { router, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function SiteSelector() {
    const { sites, activeSite } = usePage<PageProps>().props;

    const handleChange = (siteId: number) => {
        router.post(
            route('site-switch.store'),
            { site_id: siteId },
            { preserveScroll: true },
        );
    };

    return (
        <Select
            value={activeSite?.id}
            onChange={handleChange}
            style={{ minWidth: 220 }}
            placeholder="Pilih Site"
            options={sites.map((site) => ({
                value: site.id,
                label: `${site.code} — ${site.name}`,
            }))}
        />
    );
}
