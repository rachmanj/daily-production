interface SidebarMenuState {
    selectedKeys: string[];
    openKeys: string[];
}

const routeMatchers: Array<{
    prefix: string;
    selected: string;
    open?: string;
}> = [
    { prefix: '/daily-entries', selected: 'data-entry' },
    { prefix: '/excel-imports', selected: 'data-entry' },
    { prefix: '/hourly-dashboard', selected: 'hourly-dashboard', open: 'hourly' },
    { prefix: '/hourly', selected: 'hourly-entry', open: 'hourly' },
    { prefix: '/dashboard/fuel', selected: 'fuel' },
    { prefix: '/monthly-plans', selected: 'monthly-plans' },
    { prefix: '/variance', selected: 'variance' },
    { prefix: '/equipment-assignments', selected: 'equipment' },
    { prefix: '/procurement', selected: 'procurement' },
    { prefix: '/reports', selected: 'reports' },
    { prefix: '/notifications', selected: 'notifications' },
    { prefix: '/sites', selected: 'master-sites' },
    { prefix: '/pits', selected: 'master-pits' },
    { prefix: '/shifts', selected: 'master-shifts' },
    { prefix: '/fuel-types', selected: 'master-fuel-types' },
    { prefix: '/fuel-prices', selected: 'master-fuel-prices' },
    { prefix: '/users', selected: 'master-users' },
    { prefix: '/dashboard', selected: 'dashboard' },
];

export function resolveSidebarMenu(url: string): SidebarMenuState {
    const path = url.split('?')[0];

    for (const { prefix, selected, open } of routeMatchers) {
        if (path === prefix || path.startsWith(`${prefix}/`)) {
            return {
                selectedKeys: [selected],
                openKeys: open ? [open] : [],
            };
        }
    }

    return { selectedKeys: ['dashboard'], openKeys: [] };
}
