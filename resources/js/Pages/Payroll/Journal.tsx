import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';

export default function Journal({ activeTab }: PageProps<{ activeTab: string }>) {
    const tabs = [
        { label: 'Périodes', href: route('payroll.index'), active: activeTab === 'periodes' },
        { label: 'Bulletins', href: route('payroll.bulletins'), active: activeTab === 'bulletins' },
        { label: 'Rubriques', href: route('payroll.rubriques'), active: activeTab === 'rubriques' },
        { label: 'Journal de Paie', href: route('payroll.journal'), active: activeTab === 'journal' },
    ];

    return (
        <ErpLayout>
            <Head title="Journal de Paie" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Journal de Paie</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">Le journal de paie récapitulatif sera disponible dans une prochaine itération (Section 14.7 du cahier des charges).</p>
                </div>
            </div>
        </ErpLayout>
    );
}
