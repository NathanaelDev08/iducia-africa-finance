import { ReactNode, useEffect, useMemo, useState } from 'react';
import { ChevronDown, ChevronLeft, ChevronRight, ChevronUp, ChevronsLeft, ChevronsRight, Search } from 'lucide-react';

export interface Column<T = any> {
    key: string;
    label: string;
    sortable?: boolean;
    align?: 'left' | 'right' | 'center';
    render?: (row: T) => ReactNode;
    className?: string;
}

interface Props<T = any> {
    columns: Column<T>[];
    data: T[];
    title?: string;
    searchPlaceholder?: string;
    emptyMessage?: string;
    initialPerPage?: number;
    defaultSort?: { key: string; dir: 'asc' | 'desc' };
    rowKey?: (row: T) => string | number;
    toolbar?: ReactNode;
    searchable?: boolean;
}

const getVal = (row: any, key: string): any =>
    key.split('.').reduce((acc, part) => (acc == null ? acc : acc[part]), row);

export function DataTable<T = any>({
    columns, data, title, searchPlaceholder = 'Rechercher…', emptyMessage = 'Aucune donnée trouvée',
    initialPerPage = 10, defaultSort, rowKey, toolbar, searchable = true,
}: Props<T>) {
    const [search, setSearch] = useState('');
    const [sortKey, setSortKey] = useState<string | null>(defaultSort?.key ?? null);
    const [sortDir, setSortDir] = useState<'asc' | 'desc'>(defaultSort?.dir ?? 'asc');
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(initialPerPage);

    const searchKeys = useMemo(() => columns.map((c) => c.key), [columns]);

    // ── Recherche instantanée sur toutes les colonnes ──
    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        if (!q) return data;
        return data.filter((row) =>
            searchKeys.some((k) => {
                const v = getVal(row, k);
                return v != null && String(v).toLowerCase().includes(q);
            })
        );
    }, [data, search, searchKeys]);

    // ── Tri par colonne (numérique ou alphabétique) ──
    const sorted = useMemo(() => {
        if (!sortKey) return filtered;
        const arr = [...filtered];
        arr.sort((a, b) => {
            const va = getVal(a, sortKey);
            const vb = getVal(b, sortKey);
            if (va == null && vb == null) return 0;
            if (va == null) return 1;
            if (vb == null) return -1;
            if (typeof va === 'number' && typeof vb === 'number') return sortDir === 'asc' ? va - vb : vb - va;
            const sa = String(va).toLowerCase();
            const sb = String(vb).toLowerCase();
            return sortDir === 'asc' ? sa.localeCompare(sb) : sb.localeCompare(sa);
        });
        return arr;
    }, [filtered, sortKey, sortDir]);

    const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));

    useEffect(() => { setPage(1); }, [search, perPage, data.length]);
    useEffect(() => { if (page > totalPages) setPage(totalPages); }, [page, totalPages]);

    const paged = useMemo(() => sorted.slice((page - 1) * perPage, page * perPage), [sorted, page, perPage]);

    const from = sorted.length === 0 ? 0 : (page - 1) * perPage + 1;
    const to = Math.min(page * perPage, sorted.length);

    const toggleSort = (key: string) => {
        if (sortKey === key) setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'));
        else { setSortKey(key); setSortDir('asc'); }
    };

    const pageNumbers = useMemo(() => {
        const win: number[] = [];
        const start = Math.max(1, Math.min(page - 2, totalPages - 4));
        const end = Math.min(totalPages, start + 4);
        for (let i = start; i <= end; i++) win.push(i);
        return win;
    }, [page, totalPages]);

    const alignCls = (a?: string) => (a === 'right' ? 'text-right' : a === 'center' ? 'text-center' : 'text-left');

    return (
        <div>
            {/* ═══════════ BARRE D'OUTILS ═══════════ */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    {title && <h2 className="font-bold text-gray-900 dark:text-gray-100">{title}</h2>}
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {sorted.length} enregistrement(s){search ? ` filtré(s) sur ${data.length}` : ''}
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    {searchable && (
                        <div className="relative">
                            <Search className="h-4 w-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder={searchPlaceholder}
                                className="pl-9 pr-3 py-2 w-full sm:w-64 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                            />
                        </div>
                    )}
                    {toolbar}
                </div>
            </div>

            {/* ═══════════ TABLEAU ═══════════ */}
            <div className="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    onClick={col.sortable !== false ? () => toggleSort(col.key) : undefined}
                                    className={`p-3 text-xs font-semibold uppercase text-gray-600 dark:text-gray-300 ${alignCls(col.align)} ${col.sortable !== false ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-600' : ''}`}
                                    title={col.sortable !== false ? 'Cliquer pour trier' : undefined}
                                >
                                    <span className="inline-flex items-center gap-1">
                                        {col.label}
                                        {sortKey === col.key && (sortDir === 'asc' ? <ChevronUp className="h-3 w-3" /> : <ChevronDown className="h-3 w-3" />)}
                                    </span>
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        {paged.length === 0 && (
                            <tr><td colSpan={columns.length} className="p-8 text-center text-gray-400">{emptyMessage}</td></tr>
                        )}
                        {paged.map((row, idx) => (
                            <tr key={rowKey ? rowKey(row) : idx} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                {columns.map((col) => (
                                    <td key={col.key} className={`p-3 ${alignCls(col.align)} ${col.className ?? ''}`}>
                                        {col.render ? col.render(row) : (getVal(row, col.key) ?? '—')}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* ═══════════ PAGINATION ═══════════ */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4">
                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span>Afficher</span>
                    <select
                        value={perPage}
                        onChange={(e) => setPerPage(Number(e.target.value))}
                        className="rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm py-1 px-2"
                    >
                        {[10, 25, 50, 100].map((n) => <option key={n} value={n}>{n}</option>)}
                    </select>
                    <span className="whitespace-nowrap">{from}–{to} sur {sorted.length}</span>
                </div>
                <div className="flex items-center gap-1">
                    <button onClick={() => setPage(1)} disabled={page === 1} className="p-1.5 rounded-md border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700" title="Première page"><ChevronsLeft className="h-4 w-4" /></button>
                    <button onClick={() => setPage((p) => p - 1)} disabled={page === 1} className="p-1.5 rounded-md border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700" title="Précédent"><ChevronLeft className="h-4 w-4" /></button>
                    {pageNumbers.map((n) => (
                        <button
                            key={n}
                            onClick={() => setPage(n)}
                            className={`min-w-[32px] h-8 rounded-md text-sm font-medium ${n === page ? 'bg-brand-navy text-white shadow' : 'border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200'}`}
                        >
                            {n}
                        </button>
                    ))}
                    <button onClick={() => setPage((p) => p + 1)} disabled={page === totalPages} className="p-1.5 rounded-md border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700" title="Suivant"><ChevronRight className="h-4 w-4" /></button>
                    <button onClick={() => setPage(totalPages)} disabled={page === totalPages} className="p-1.5 rounded-md border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-100 dark:hover:bg-gray-700" title="Dernière page"><ChevronsRight className="h-4 w-4" /></button>
                </div>
            </div>
        </div>
    );
}

export default DataTable;
