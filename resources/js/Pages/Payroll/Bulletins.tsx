import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Payslip { id: number; slip_number: string | null; base_salary: number; net_salary: number; status: string; employee: { first_name: string; last_name: string; matricule: string }; pay_run: { name: string }; }

const formatMoney = (amount: number) => new Intl.NumberFormat('fr-FR').format(amount || 0) + ' FCFA';

const STATUS_CONFIG: Record<string, { label: string; color: string }> = {
    draft: { label: 'Brouillon', color: 'bg-gray-100 text-gray-800' },
    calculated: { label: 'Calculé', color: 'bg-yellow-100 text-yellow-800' },
    validated: { label: 'Validé', color: 'bg-green-100 text-green-800' },
    locked: { label: 'Verrouillé', color: 'bg-red-100 text-red-800' },
};

export default function Bulletins({ payslips, activeTab }: PageProps<{ payslips: Payslip[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');
    const tabs = [
        { label: 'Périodes', href: route('payroll.index'), active: activeTab === 'periodes' },
        { label: 'Bulletins', href: route('payroll.bulletins'), active: activeTab === 'bulletins' },
        { label: 'Rubriques', href: route('payroll.rubriques'), active: activeTab === 'rubriques' },
        { label: 'Journal de Paie', href: route('payroll.journal'), active: activeTab === 'journal' },
    ];

    return (
        <ErpLayout>
            <Head title="Bulletins" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Bulletins de Paie</h1>
                <Tabs tabs={tabs} />
                <div className="mb-3 flex justify-end">
                    <ViewSwitcher value={view} onChange={setView} />
                </div>
                {view === 'list' ? (
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Employé</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Période</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Salaire Base</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Net à Payer</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {payslips.length === 0 ? (
                                <tr><td colSpan={5} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun bulletin.</td></tr>
                            ) : (
                                payslips.map((p) => (
                                    <tr key={p.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{p.employee.first_name} {p.employee.last_name} ({p.employee.matricule})</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{p.pay_run.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{formatMoney(p.base_salary)}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">{formatMoney(p.net_salary)}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${p.status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                                {p.status}
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
                        data={payslips}
                        rowKey={(p) => p.id}
                        groupBy={(p) => STATUS_CONFIG[p.status] ? p.status : 'draft'}
                        columns={Object.entries(STATUS_CONFIG).map(([key, cfg]) => ({ key, label: cfg.label, colorClass: cfg.color }))}
                        emptyMessage="Aucun bulletin."
                        renderCard={(p) => (
                            <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                <div className="font-medium text-gray-900">{p.employee.first_name} {p.employee.last_name}</div>
                                <div className="text-xs text-gray-500 font-mono">{p.employee.matricule}</div>
                                <div className="mt-1 text-xs text-gray-600">{p.pay_run.name}</div>
                                <div className="mt-2 flex items-center justify-between text-xs">
                                    <span className="text-gray-500">Base: {formatMoney(p.base_salary)}</span>
                                    <span className="font-semibold text-green-600">{formatMoney(p.net_salary)}</span>
                                </div>
                                <div className="mt-2">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${p.status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                        {p.status}
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
