import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { FileText, BookOpen, Notebook, Scale, List } from 'lucide-react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Journal { id: number; code: string; name: string; type: string; is_active: boolean; }

export default function Journaux({ journals, activeTab }: PageProps<{ journals: Journal[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');

    const tabs = [
        { label: 'Écritures', href: route('accounting.index'), active: activeTab === 'ecritures', icon: FileText },
        { label: 'Plan Comptable', href: route('accounting.plan'), active: activeTab === 'plan', icon: List },
        { label: 'Journaux', href: route('accounting.journals'), active: activeTab === 'journaux', icon: Notebook },
        { label: 'Balance', href: route('accounting.balance'), active: activeTab === 'balance', icon: Scale },
        { label: 'Grand Livre', href: route('accounting.grand-livre'), active: activeTab === 'grand-livre', icon: BookOpen },
    ];

    return (
        <ErpLayout>
            <Head title="Journaux" />
            <div className="py-2">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Journaux Comptables</h1>
                    <ViewSwitcher value={view} onChange={setView} />
                </div>
                <Tabs tabs={tabs} />
                {view === 'list' ? (
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nom</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {journals.length === 0 ? (
                                <tr><td colSpan={4} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun journal.</td></tr>
                            ) : (
                                journals.map((journal) => (
                                    <tr key={journal.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{journal.code}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{journal.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{journal.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${journal.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {journal.is_active ? 'Actif' : 'Inactif'}
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
                    data={journals}
                    rowKey={(j) => j.id}
                    groupBy={(j) => (j.is_active ? 'active' : 'inactive')}
                    columns={[
                        { key: 'active', label: 'Actifs', colorClass: 'bg-green-100 text-green-800' },
                        { key: 'inactive', label: 'Inactifs', colorClass: 'bg-red-100 text-red-800' },
                    ]}
                    emptyMessage="Aucun journal."
                    renderCard={(journal) => (
                        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div className="font-mono text-xs text-gray-500 dark:text-gray-400">{journal.code}</div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">{journal.name}</div>
                            <div className="mt-1 text-xs text-gray-500 dark:text-gray-300">{journal.type}</div>
                        </div>
                    )}
                />
                )}
            </div>
        </ErpLayout>
    );
}
