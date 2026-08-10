import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { Users, FileSignature, Building2, CalendarDays } from 'lucide-react';

interface Contract { id: number; contract_number: string | null; start_date: string; end_date: string | null; base_salary: number; status: string; employee: { first_name: string; last_name: string }; contract_type: { name: string }; }

const formatMoney = (amount: number) => new Intl.NumberFormat('fr-FR').format(amount || 0) + ' FCFA';

export default function Contrats({ contracts, activeTab }: PageProps<{ contracts: Contract[], activeTab: string }>) {
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
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Contrats de Travail</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Employé</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Début</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fin</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Salaire</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {contracts.length === 0 ? (
                                <tr><td colSpan={6} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun contrat.</td></tr>
                            ) : (
                                contracts.map((c) => (
                                    <tr key={c.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{c.employee.first_name} {c.employee.last_name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.contract_type.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.start_date}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{c.end_date || 'Indéterminé'}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">{formatMoney(c.base_salary)}</td>
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
            </div>
        </ErpLayout>
    );
}
