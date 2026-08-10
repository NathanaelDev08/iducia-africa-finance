import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { Users, FileSignature, Building2, CalendarDays } from 'lucide-react';

export default function Conges({ activeTab }: PageProps<{ activeTab: string }>) {
    const tabs = [
        { label: 'Employés', href: route('hr.index'), active: activeTab === 'employes', icon: Users },
        { label: 'Contrats', href: route('hr.contrats'), active: activeTab === 'contrats', icon: FileSignature },
        { label: 'Départements', href: route('hr.departements'), active: activeTab === 'departements', icon: Building2 },
        { label: 'Congés', href: route('hr.conges'), active: activeTab === 'conges', icon: CalendarDays },
    ];

    return (
        <ErpLayout>
            <Head title="Congés" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Gestion des Congés</h1>
                <Tabs tabs={tabs} />
                <div className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">Le module de gestion des congés et absences sera disponible dans une prochaine itération (Section 13.5 du cahier des charges).</p>
                </div>
            </div>
        </ErpLayout>
    );
}
