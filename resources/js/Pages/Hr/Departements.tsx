import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { Users, FileSignature, Building2, CalendarDays } from 'lucide-react';

interface Department { id: number; code: string; name: string; employees_count: number; is_active: boolean; }

export default function Departements({ departments, activeTab }: PageProps<{ departments: Department[], activeTab: string }>) {
    const tabs = [
        { label: 'Employés', href: route('hr.index'), active: activeTab === 'employes', icon: Users },
        { label: 'Contrats', href: route('hr.contrats'), active: activeTab === 'contrats', icon: FileSignature },
        { label: 'Départements', href: route('hr.departements'), active: activeTab === 'departements', icon: Building2 },
        { label: 'Congés', href: route('hr.conges'), active: activeTab === 'conges', icon: CalendarDays },
    ];

    return (
        <ErpLayout>
            <Head title="Départements" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Départements & Services</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nom</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Effectif</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {departments.length === 0 ? (
                                <tr><td colSpan={4} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun département.</td></tr>
                            ) : (
                                departments.map((dept) => (
                                    <tr key={dept.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{dept.code}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{dept.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{dept.employees_count}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${dept.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {dept.is_active ? 'Actif' : 'Inactif'}
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
