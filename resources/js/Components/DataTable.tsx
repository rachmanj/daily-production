import { ProTable, ProTableProps } from '@ant-design/pro-components';
import { ReactNode } from 'react';

type DataTableProps<T extends Record<string, any>> = ProTableProps<T, Record<string, unknown>> & {
    headerTitle?: ReactNode;
};

export default function DataTable<T extends Record<string, any>>({
    headerTitle,
    search = false,
    options = { density: true, fullScreen: true, reload: false },
    pagination = { defaultPageSize: 20, showSizeChanger: true },
    ...props
}: DataTableProps<T>) {
    return (
        <ProTable<T>
            headerTitle={headerTitle}
            search={search}
            options={options}
            pagination={pagination}
            rowKey="id"
            cardBordered
            {...props}
        />
    );
}
