import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { FileText, BookOpen, Notebook, Scale, List } from 'lucide-react';

export default function Balance({ activeTab }: PageProps<{ activeTab: string }>) {
    const tabs = [
        { label: 'Écritures', href: route('accounting.index'), active: activeTab === 'ecritures', icon: FileText },
        { label: 'Plan Comptable', href: route('accounting.plan'), active: activeTab === 'plan', icon: List },
        { label: 'Journaux', href: route('accounting.journals'), active: activeTab === 'journaux', icon: Notebook },
        { label: 'Balance', href: route('accounting.balance'), active: activeTab === 'balance', icon: Scale },
        { label: 'Grand Livre', href: route('accounting.grand-livre'), active: activeTab === 'grand-livre', icon: BookOpen },
    ];

    return (
        <ErpLayout>
            <Head title="Balance" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Balance Générale</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">La balance générale affiche les soldes de tous les comptes. Utilisez le lien ci-dessous pour consulter la balance complète via l'API.</p>
                    <a href={route('reporting.trial-balance')} target="_blank" className="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Consulter la Balance (JSON)
                    </a>
                </div>
            </div>
        </ErpLayout>
    );
}
