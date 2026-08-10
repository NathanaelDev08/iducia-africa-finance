import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';

export default function Parametres({ activeTab }: PageProps<{ activeTab: string }>) {
    const tabs = [
        { label: 'Déclarations TVA', href: route('tax.index'), active: activeTab === 'declarations' },
        { label: 'Échéancier', href: route('tax.echeancier'), active: activeTab === 'echeancier' },
        { label: 'Paramètres', href: route('tax.parametres'), active: activeTab === 'parametres' },
    ];

    return (
        <ErpLayout>
            <Head title="Paramètres Fiscaux" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Paramètres Fiscaux</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">Le paramétrage fiscal (taux, régimes, retenues) sera disponible dans une prochaine itération (Section 12.2 du cahier des charges).</p>
                </div>
            </div>
        </ErpLayout>
    );
}
