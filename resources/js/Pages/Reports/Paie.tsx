import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';

export default function Paie({ activeTab }: PageProps<{ activeTab: string }>) {
    const tabs = [
        { label: 'Comptables', href: route('reporting.index'), active: activeTab === 'comptables' },
        { label: 'RH', href: route('reporting.rh'), active: activeTab === 'rh' },
        { label: 'Paie', href: route('reporting.paie'), active: activeTab === 'paie' },
        { label: 'Fiscaux', href: route('reporting.fiscaux'), active: activeTab === 'fiscaux' },
    ];

    return (
        <ErpLayout>
            <Head title="Rapports Paie" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Rapports Paie</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">Les rapports de paie (journal de paie, cotisations, charges patronales) seront disponibles dans une prochaine itération.</p>
                </div>
            </div>
        </ErpLayout>
    );
}
