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
    { prefix: '/dashboard/fuel', selected: 'fuel' },
    { prefix: '/monthly-plans', selected: 'monthly-plans', open: 'plan' },
    { prefix: '/variance', selected: 'variance', open: 'plan' },
    { prefix: '/equipment-assignments', selected: 'equipment' },
    { prefix: '/procurement', selected: 'procurement' },
    { prefix: '/reports', selected: 'reports' },
    { prefix: '/notifications', selected: 'notifications' },
    { prefix: '/sites', selected: 'master-sites', open: 'master' },
    { prefix: '/pits', selected: 'master-pits', open: 'master' },
    { prefix: '/shifts', selected: 'master-shifts', open: 'master' },
    { prefix: '/fuel-types', selected: 'master-fuel-types', open: 'master' },
    { prefix: '/fuel-prices', selected: 'master-fuel-prices', open: 'master' },
    { prefix: '/users', selected: 'master-users', open: 'master' },
    { prefix: '/roles', selected: 'master-roles', open: 'master' },
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
