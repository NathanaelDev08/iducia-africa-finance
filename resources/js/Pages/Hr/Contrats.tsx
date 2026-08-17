import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { Users, FileSignature, Building2, CalendarDays } from 'lucide-react';
import { useState } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Contract { id: number; contract_number: string | null; start_date: string; end_date: string | null; base_salary: number; salaire_categoriel: number | null; sursalaire: number | null; has_cmu: boolean; has_cnps: boolean; status: string; employee: { first_name: string; last_name: string }; contract_type: { name: string }; }

const formatMoney = (amount: number) => new Intl.NumberFormat('fr-FR').format(amount || 0) + ' FCFA';

const CONTRACT_TYPE_CONFIG: Record<string, { label: string; color: string }> = {
    'CDI': { label: 'CDI', color: 'bg-green-100 text-green-800' },
    'CDD': { label: 'CDD', color: 'bg-blue-100 text-blue-800' },
    'Stage': { label: 'Stage', color: 'bg-purple-100 text-purple-800' },
    "Période d'essai": { label: "Période d'essai", color: 'bg-yellow-100 text-yellow-800' },
    'Contrat saisonnier': { label: 'Saisonnier', color: 'bg-orange-100 text-orange-800' },
};
const OTHER_TYPE = { key: 'other', label: 'Autre', color: 'bg-gray-100 text-gray-800' };

export default function Contrats({ contracts, activeTab }: PageProps<{ contracts: Contract[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');
    const tabs = [
        { label: 'Employés', href: route('hr.index'), active: activeTab === 'employes', icon: Users },
        { label: 'Contrats', href: route('hr.contrats'), active: activeTab === 'contrats', icon: FileSignature },
        { label: 'Départements', href: route('hr.departements'), active: activeTab === 'departements', icon: Building2 },
        { label: 'Congés', href: route('hr.conges'), active: activeTab === 'conges', icon: CalendarDays },
    ];

    return (
        <ErpLayout>
            <Head title="Contrats" />
            <div className="py-2">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Contrats de Travail</h1>
                    <ViewSwitcher value={view} onChange={setView} />
                </div>
                <Tabs tabs={tabs} />
                {view === 'list' ? (
                    <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Employé</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Début</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fin</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Catégoriel</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sursalaire</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Salaire base</th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cotisations</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {contracts.length === 0 ? (
                                    <tr><td colSpan={9} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun contrat.</td></tr>
                                ) : (
                                    contracts.map((c) => (
                                        <tr key={c.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{c.employee.first_name} {c.employee.last_name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.contract_type.name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.start_date}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.end_date || 'Indéterminé'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{c.salaire_categoriel != null ? formatMoney(c.salaire_categoriel) : '—'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{c.sursalaire != null ? formatMoney(c.sursalaire) : '—'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{formatMoney(c.base_salary)}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-center">
                                                <div className="flex gap-1 justify-center">
                                                    {c.has_cnps && <span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">CNPS</span>}
                                                    {c.has_cmu && <span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">CMU</span>}
                                                    {!c.has_cnps && !c.has_cmu && <span className="text-xs text-gray-400">—</span>}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${c.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                    {c.status}
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
                        data={contracts}
                        rowKey={(c) => c.id}
                        groupBy={(c) => (CONTRACT_TYPE_CONFIG[c.contract_type?.name] ? c.contract_type.name : OTHER_TYPE.key)}
                        columns={[
                            ...Object.entries(CONTRACT_TYPE_CONFIG).map(([key, cfg]) => ({ key, label: cfg.label, colorClass: cfg.color })),
                            { key: OTHER_TYPE.key, label: OTHER_TYPE.label, colorClass: OTHER_TYPE.color },
                        ]}
                        emptyMessage="Aucun contrat"
                        renderCard={(c) => {
                            const typeCfg = CONTRACT_TYPE_CONFIG[c.contract_type?.name] || OTHER_TYPE;
                            return (
                                <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="font-medium text-gray-900">{c.employee.first_name} {c.employee.last_name}</div>
                                        <span className={'px-2 py-1 rounded-full text-xs font-semibold ' + typeCfg.color}>{typeCfg.label}</span>
                                    </div>
                                    <div className="mt-2 text-xs text-gray-600">
                                        Du {c.start_date} au {c.end_date || 'Indéterminé'}
                                    </div>
                                    <div className="mt-1 text-xs text-gray-500">
                                        {formatMoney(c.base_salary)}
                                        {(c.salaire_categoriel != null || c.sursalaire != null) && (
                                            <span className="text-gray-400"> (cat. {c.salaire_categoriel != null ? formatMoney(c.salaire_categoriel) : '—'} + sur. {c.sursalaire != null ? formatMoney(c.sursalaire) : '—'})</span>
                                        )}
                                    </div>
                                    <div className="mt-2 flex items-center gap-2 border-t border-gray-100 pt-2">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${c.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                            {c.status}
                                        </span>
                                        {c.has_cnps && <span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">CNPS</span>}
                                        {c.has_cmu && <span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">CMU</span>}
                                    </div>
                                </div>
                            );
                        }}
                    />
                )}
            </div>
        </ErpLayout>
    );
}
