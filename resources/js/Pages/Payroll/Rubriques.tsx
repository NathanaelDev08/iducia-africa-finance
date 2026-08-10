import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';

interface PayItem { id: number; code: string; name: string; type: string; is_active: boolean; }

export default function Rubriques({ items, activeTab }: PageProps<{ items: PayItem[], activeTab: string }>) {
    const tabs = [
        { label: 'Périodes', href: route('payroll.index'), active: activeTab === 'periodes' },
        { label: 'Bulletins', href: route('payroll.bulletins'), active: activeTab === 'bulletins' },
        { label: 'Rubriques', href: route('payroll.rubriques'), active: activeTab === 'rubriques' },
        { label: 'Journal de Paie', href: route('payroll.journal'), active: activeTab === 'journal' },
    ];

    const typeLabels: { [key: string]: string } = {
        'earning': 'Gain',
        'deduction': 'Retenue',
        'employee_contribution': 'Cot. Salariale',
        'employer_contribution': 'Cot. Patronale',
        'tax': 'Impôt',
    };

    return (
        <ErpLayout>
            <Head title="Rubriques de Paie" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Rubriques de Paie</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Libellé</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {items.length === 0 ? (
                                <tr><td colSpan={4} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucune rubrique.</td></tr>
                            ) : (
                                items.map((item) => (
                                    <tr key={item.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{item.code}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{item.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{typeLabels[item.type] || item.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {item.is_active ? 'Actif' : 'Inactif'}
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
