import { usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { PageProps } from '@/types';

type PermissionGateProps = PropsWithChildren<{
    permission?: string;
    role?: string;
    fallback?: React.ReactNode;
}>;

export default function PermissionGate({
    children,
    permission,
    role,
    fallback = null,
}: PermissionGateProps) {
    const { auth } = usePage<PageProps>().props;
    const permissions = auth.permissions ?? auth.user?.permissions ?? [];
    const roles = auth.user?.roles ?? [];

    if (permission && !permissions.includes(permission)) {
        return <>{fallback}</>;
    }

    if (role && !roles.includes(role)) {
        return <>{fallback}</>;
    }

    return <>{children}</>;
}
