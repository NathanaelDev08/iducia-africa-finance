import ErpLayout from '@/Layouts/ErpLayout';
import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SelectInput from '@/Components/SelectInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Users, FileSignature, Building2, CalendarDays } from 'lucide-react';

interface Department { id: number; name: string; }
interface Position { id: number; name: string; department_id: number | null; }
interface ContractType { id: number; name: string; }

interface Props extends PageProps {
    departments: Department[];
    positions: Position[];
    contractTypes: ContractType[];
    activeTab: string;
}

export default function CreateEmploye({ departments, positions, contractTypes, activeTab, errors }: Props) {
    const tabs = [
        { label: 'Employés', href: route('hr.index'), active: activeTab === 'employes', icon: Users },
        { label: 'Contrats', href: route('hr.contrats'), active: activeTab === 'contrats', icon: FileSignature },
        { label: 'Départements', href: route('hr.departements'), active: activeTab === 'departements', icon: Building2 },
        { label: 'Congés', href: route('hr.conges'), active: activeTab === 'conges', icon: CalendarDays },
    ];

    const { data, setData, post, processing, reset } = useForm({
        last_name: '',
        first_name: '',
        matricule: '',
        birth_date: '',
        birth_place: '',
        sex: '',
        nationality: '',
        id_card_number: '',
        cnps_number: '',
        tax_id: '',
        address: '',
        phone: '',
        email: '',
        marital_status: '',
        dependents_count: 0,
        hire_date: new Date().toISOString().split('T')[0],
        seniority_date: '',
        department_id: '',
        position_id: '',
        professional_category: '',
        bank_name: '',
        bank_account: '',
        mobile_money: '',
        payment_method: 'bank',
        contract: {
            contract_type_id: '',
            start_date: new Date().toISOString().split('T')[0],
            end_date: '',
            trial_period_end_date: '',
            working_hours_per_week: 40,
            base_salary: '',
        },
    });

    const filteredPositions = data.department_id
        ? positions.filter(p => p.department_id === parseInt(String(data.department_id)))
        : positions;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('hr.employes.store'));
    };

    return (
        <ErpLayout>
            <Head title="Nouvel Employé" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Nouvel Employé</h1>
                <Tabs tabs={tabs} />

                <form onSubmit={handleSubmit} className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    {/* Informations personnelles */}
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informations personnelles</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel required>Nom</InputLabel>
                            <TextInput type="text" className="w-full" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} />
                            <InputError message={errors.last_name} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel required>Prénom</InputLabel>
                            <TextInput type="text" className="w-full" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} />
                            <InputError message={errors.first_name} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel>Matricule (auto si vide)</InputLabel>
                            <TextInput type="text" className="w-full" value={data.matricule} onChange={(e) => setData('matricule', e.target.value)} placeholder="Laisser vide pour auto-génération" />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel>Date de naissance</InputLabel>
                            <TextInput type="date" className="w-full" value={data.birth_date} onChange={(e) => setData('birth_date', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>Lieu de naissance</InputLabel>
                            <TextInput type="text" className="w-full" value={data.birth_place} onChange={(e) => setData('birth_place', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>Sexe</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.sex}
                                onChange={(e) => setData('sex', e.target.value)}
                                options={[
                                    { value: 'M', label: 'Masculin' },
                                    { value: 'F', label: 'Féminin' },
                                    { value: 'Autre', label: 'Autre' },
                                ]}
                                placeholder="Sélectionner"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel>Nationalité</InputLabel>
                            <TextInput type="text" className="w-full" value={data.nationality} onChange={(e) => setData('nationality', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>N° Pièce d'identité</InputLabel>
                            <TextInput type="text" className="w-full" value={data.id_card_number} onChange={(e) => setData('id_card_number', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>N° CNPS</InputLabel>
                            <TextInput type="text" className="w-full" value={data.cnps_number} onChange={(e) => setData('cnps_number', e.target.value)} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel>Adresse</InputLabel>
                            <TextInput type="text" className="w-full" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>Téléphone</InputLabel>
                            <TextInput type="tel" className="w-full" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="+225 XX XX XX XX XX" />
                        </div>
                    </div>

                    {/* Informations professionnelles */}
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 mt-8">Informations professionnelles</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel required>Date d'embauche</InputLabel>
                            <TextInput type="date" className="w-full" value={data.hire_date} onChange={(e) => setData('hire_date', e.target.value)} />
                            <InputError message={errors.hire_date} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel>Département</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.department_id}
                                onChange={(e) => setData('department_id', e.target.value)}
                                options={departments.map(d => ({ value: d.id, label: d.name }))}
                                placeholder="Sélectionner"
                            />
                        </div>
                        <div>
                            <InputLabel>Poste</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.position_id}
                                onChange={(e) => setData('position_id', e.target.value)}
                                options={filteredPositions.map(p => ({ value: p.id, label: p.name }))}
                                placeholder="Sélectionner"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel>Catégorie professionnelle</InputLabel>
                            <TextInput type="text" className="w-full" value={data.professional_category} onChange={(e) => setData('professional_category', e.target.value)} />
                        </div>
                        <div>
                            <InputLabel>Méthode de paiement</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.payment_method}
                                onChange={(e) => setData('payment_method', e.target.value)}
                                options={[
                                    { value: 'bank', label: 'Virement bancaire' },
                                    { value: 'mobile_money', label: 'Mobile Money' },
                                    { value: 'cash', label: 'Espèces' },
                                ]}
                            />
                        </div>
                        <div>
                            <InputLabel>N° Compte bancaire</InputLabel>
                            <TextInput type="text" className="w-full" value={data.bank_account} onChange={(e) => setData('bank_account', e.target.value)} />
                        </div>
                    </div>

                    {/* Contrat de travail */}
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 mt-8">Contrat de travail</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel required>Type de contrat</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.contract.contract_type_id}
                                onChange={(e) => setData('contract', { ...data.contract, contract_type_id: e.target.value })}
                                options={contractTypes.map(ct => ({ value: ct.id, label: ct.name }))}
                                placeholder="Sélectionner"
                            />
                            <InputError message={errors['contract.contract_type_id']} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel required>Date de début</InputLabel>
                            <TextInput type="date" className="w-full" value={data.contract.start_date} onChange={(e) => setData('contract', { ...data.contract, start_date: e.target.value })} />
                        </div>
                        <div>
                            <InputLabel>Date de fin (CDD)</InputLabel>
                            <TextInput type="date" className="w-full" value={data.contract.end_date} onChange={(e) => setData('contract', { ...data.contract, end_date: e.target.value })} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <InputLabel required>Salaire de base (FCFA)</InputLabel>
                            <TextInput type="number" className="w-full" value={data.contract.base_salary} onChange={(e) => setData('contract', { ...data.contract, base_salary: e.target.value })} min="0" step="1" />
                            <InputError message={errors['contract.base_salary']} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel>Heures/semaine</InputLabel>
                            <TextInput type="number" className="w-full" value={data.contract.working_hours_per_week} onChange={(e) => setData('contract', { ...data.contract, working_hours_per_week: parseInt(e.target.value) || 0 })} min="0" max="168" />
                        </div>
                        <div>
                            <InputLabel>Fin période d'essai</InputLabel>
                            <TextInput type="date" className="w-full" value={data.contract.trial_period_end_date} onChange={(e) => setData('contract', { ...data.contract, trial_period_end_date: e.target.value })} />
                        </div>
                    </div>

                    {/* Boutons */}
                    <div className="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <SecondaryButton type="button" onClick={() => reset()}>
                            Réinitialiser
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            {processing ? 'Enregistrement...' : 'Créer l\'employé'}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </ErpLayout>
    );
}
