import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { useState } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Deadline { id: number; name: string; type: string; due_date: string; status: string; }

type DateBucket = 'late' | 'this_week' | 'this_month' | 'later';

const startOfDay = (d: Date) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };

function dateBucket(dueDate: string): DateBucket {
    const today = startOfDay(new Date());
    const due = startOfDay(new Date(dueDate));
    const diffDays = Math.floor((due.getTime() - today.getTime()) / 86400000);
    if (diffDays < 0) return 'late';
    if (diffDays <= 7) return 'this_week';
    if (due.getMonth() === today.getMonth() && due.getFullYear() === today.getFullYear()) return 'this_month';
    return 'later';
}

/** Une échéance déjà déclarée/payée n'est plus "urgente", même si sa date est passée. */
function groupKey(d: Deadline): DateBucket | 'done' {
    if (d.status === 'paid' || d.status === 'filed' || d.status === 'done') return 'done';
    return dateBucket(d.due_date);
}

const bucketColors: Record<DateBucket | 'done', string> = {
    late: 'bg-red-100 text-red-800',
    this_week: 'bg-amber-100 text-amber-800',
    this_month: 'bg-blue-100 text-blue-800',
    later: 'bg-gray-100 text-gray-700',
    done: 'bg-green-100 text-green-800',
};

export default function Echeancier({ deadlines, activeTab }: PageProps<{ deadlines: Deadline[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');

    const tabs = [
        { label: 'Déclarations TVA', href: route('tax.index'), active: activeTab === 'declarations' },
        { label: 'Échéancier', href: route('tax.echeancier'), active: activeTab === 'echeancier' },
        { label: 'Paramètres', href: route('tax.parametres'), active: activeTab === 'parametres' },
    ];

    return (
        <ErpLayout>
            <Head title="Échéancier Fiscal" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Échéancier Fiscal</h1>
                <Tabs tabs={tabs} />
                <div className="flex items-center justify-end mb-4">
                    <ViewSwitcher value={view} onChange={setView} />
                </div>
                {view === 'list' ? (
                    <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Obligation</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Échéance</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {deadlines.length === 0 ? (
                                    <tr><td colSpan={4} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucune échéance.</td></tr>
                                ) : (
                                    deadlines.map((d) => (
                                        <tr key={d.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{d.name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{d.type}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{d.due_date}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${d.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}`}>
                                                    {d.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <KanbanBoard
                        data={deadlines}
                        rowKey={(d) => d.id}
                        groupBy={(d) => groupKey(d)}
                        columns={[
                            { key: 'late', label: 'En retard', colorClass: bucketColors.late },
                            { key: 'this_week', label: 'Cette semaine', colorClass: bucketColors.this_week },
                            { key: 'this_month', label: 'Ce mois-ci', colorClass: bucketColors.this_month },
                            { key: 'later', label: 'Plus tard', colorClass: bucketColors.later },
                            { key: 'done', label: 'Traitée', colorClass: bucketColors.done },
                        ]}
                        emptyMessage="Aucune échéance"
                        renderCard={(d) => (
                            <div className="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 shadow-sm">
                                <div className="font-medium text-sm text-gray-900 dark:text-gray-100">{d.name}</div>
                                <div className="mt-1 text-xs text-gray-500 dark:text-gray-300">{d.type}</div>
                                <div className="mt-2 flex items-center justify-between">
                                    <span className="text-xs text-gray-500 dark:text-gray-400">{d.due_date}</span>
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${d.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}`}>
                                        {d.status}
                                    </span>
                                </div>
                            </div>
                        )}
                    />
                )}
            </div>
        </ErpLayout>
    );
}
