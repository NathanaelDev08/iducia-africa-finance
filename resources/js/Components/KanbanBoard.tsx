import { ReactNode } from 'react';

export interface KanbanColumn {
    key: string;
    label: string;
    colorClass?: string;
}

interface Props<T = any> {
    data: T[];
    columns: KanbanColumn[];
    groupBy: (row: T) => string;
    renderCard: (row: T) => ReactNode;
    rowKey?: (row: T) => string | number;
    emptyMessage?: string;
}

export function KanbanBoard<T = any>({
    data, columns, groupBy, renderCard, rowKey, emptyMessage = 'Aucune donnée',
}: Props<T>) {
    if (data.length === 0) {
        return <div className="p-8 text-center text-gray-400 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">{emptyMessage}</div>;
    }

    const grouped = columns.map((col) => ({
        ...col,
        items: data.filter((row) => groupBy(row) === col.key),
    }));

    return (
        <div className="flex gap-4 overflow-x-auto pb-2">
            {grouped.map((col) => (
                <div key={col.key} className="w-72 flex-shrink-0">
                    <div className={`flex items-center justify-between rounded-t-lg px-3 py-2 text-sm font-semibold ${col.colorClass ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'}`}>
                        <span>{col.label}</span>
                        <span className="rounded-full bg-white/60 dark:bg-black/20 px-2 py-0.5 text-xs">{col.items.length}</span>
                    </div>
                    <div className="min-h-[80px] space-y-2 rounded-b-lg border border-t-0 border-gray-200 bg-gray-50/50 p-2 dark:border-gray-700 dark:bg-gray-900/30">
                        {col.items.length === 0 && <div className="py-4 text-center text-xs text-gray-400">—</div>}
                        {col.items.map((row, idx) => (
                            <div key={rowKey ? rowKey(row) : idx}>{renderCard(row)}</div>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}

export default KanbanBoard;
