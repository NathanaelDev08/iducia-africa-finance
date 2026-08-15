import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { FileText, BookOpen, Notebook, Scale, List } from 'lucide-react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Account { id: number; number: string; name: string; class_number: number; type: string; is_active: boolean; }

export default function PlanComptable({ accounts, activeTab }: PageProps<{ accounts: Account[], activeTab: string }>) {
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
            <Head title="Plan Comptable" />
            <div className="py-2">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Plan Comptable SYSCOHADA</h1>
                    <ViewSwitcher value={view} onChange={setView} />
                </div>
                <Tabs tabs={tabs} />
                {view === 'list' ? (
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Compte</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Intitulé</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Classe</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {accounts.length === 0 ? (
                                <tr><td colSpan={5} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun compte.</td></tr>
                            ) : (
                                accounts.map((account) => (
                                    <tr key={account.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{account.number}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{account.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{account.class_number}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{account.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {account.is_active ? 'Actif' : 'Inactif'}
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
                    data={accounts}
                    rowKey={(a) => a.id}
                    groupBy={(a) => (a.is_active ? 'active' : 'inactive')}
                    columns={[
                        { key: 'active', label: 'Actifs', colorClass: 'bg-green-100 text-green-800' },
                        { key: 'inactive', label: 'Inactifs', colorClass: 'bg-red-100 text-red-800' },
                    ]}
                    emptyMessage="Aucun compte."
                    renderCard={(account) => (
                        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div className="font-mono text-xs text-gray-500 dark:text-gray-400">{account.number}</div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">{account.name}</div>
                            <div className="mt-1 flex justify-between text-xs text-gray-500 dark:text-gray-300">
                                <span>Classe {account.class_number}</span>
                                <span>{account.type}</span>
                            </div>
                        </div>
                    )}
                />
                )}
            </div>
        </ErpLayout>
    );
}
