import ErpLayout from '@/Layouts/ErpLayout';
import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Calendar, FileText, List, BookOpen } from 'lucide-react';

export default function CreatePeriode({ activeTab, errors }: any) {
    const tabs = [
        { label: 'Périodes', href: route('payroll.index'), active: activeTab === 'periodes', icon: Calendar },
        { label: 'Bulletins', href: route('payroll.bulletins'), active: activeTab === 'bulletins', icon: FileText },
        { label: 'Rubriques', href: route('payroll.rubriques'), active: activeTab === 'rubriques', icon: List },
        { label: 'Journal de Paie', href: route('payroll.journal'), active: activeTab === 'journal', icon: BookOpen },
    ];

    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];

    const { data, setData, post, processing, reset } = useForm({
        name: `Paie ${now.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })}`,
        reference: `PAIE-${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}`,
        period_start: firstDay,
        period_end: lastDay,
        payment_date: lastDay,
        notes: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('payroll.periodes.store'));
    };

    return (
        <ErpLayout>
            <Head title="Nouvelle Période de Paie" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Nouvelle Période de Paie</h1>
                <Tabs tabs={tabs} />

                <form onSubmit={handleSubmit} className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 max-w-2xl">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel required>Nom de la période</InputLabel>
                            <TextInput type="text" className="w-full" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                            <InputError message={errors.name} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel>Référence</InputLabel>
                            <TextInput type="text" className="w-full" value={data.reference} onChange={(e) => setData('reference', e.target.value)} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel required>Début de période</InputLabel>
                            <TextInput type="date" className="w-full" value={data.period_start} onChange={(e) => setData('period_start', e.target.value)} />
                            <InputError message={errors.period_start} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel required>Fin de période</InputLabel>
                            <TextInput type="date" className="w-full" value={data.period_end} onChange={(e) => setData('period_end', e.target.value)} />
                            <InputError message={errors.period_end} className="mt-1" />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel>Date de paiement</InputLabel>
                            <TextInput type="date" className="w-full" value={data.payment_date} onChange={(e) => setData('payment_date', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>Notes</InputLabel>
                            <TextInput type="text" className="w-full" value={data.notes} onChange={(e) => setData('notes', e.target.value)} placeholder="Notes optionnelles" />
                        </div>
                    </div>

                    <div className="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <SecondaryButton type="button" onClick={() => reset()}>
                            Réinitialiser
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            {processing ? 'Création...' : 'Créer la période'}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </ErpLayout>
    );
}
