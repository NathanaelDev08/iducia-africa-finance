import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Employee {
    id: number;
    matricule: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    sex: string | null;
    hire_date: string;
    status: string;
    department: { id: number; name: string } | null;
    position: { id: number; name: string } | null;
    contracts_count: number;
}

interface Department { id: number; name: string; }

interface Props {
    employees: Employee[];
    departments: Department[];
    stats: { total: number; active: number; inactive: number };
    filters: { search?: string; status?: string; department?: string };
}

const STATUS_CONFIG: Record<string, { label: string; color: string }> = {
    active: { label: 'Actif', color: 'bg-green-100 text-green-800' },
    inactive: { label: 'Inactif', color: 'bg-gray-100 text-gray-800' },
    suspended: { label: 'Suspendu', color: 'bg-yellow-100 text-yellow-800' },
    terminated: { label: 'Terminé', color: 'bg-red-100 text-red-800' },
};

export default function Index({ employees, departments, stats, filters }: Props) {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [view, setView] = useState<ViewMode>('list');
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [department, setDepartment] = useState(filters.department || '');

    const applyFilters = () => {
        router.get(route('hr.employees.index'), {
            search: search || undefined,
            status: status || undefined,
            department: department || undefined,
        }, { preserveState: true });
    };

    const resetFilters = () => {
        setSearch(''); setStatus(''); setDepartment('');
        router.get(route('hr.employees.index'));
    };

    return (
        <ErpLayout>
            <Head title="Employés" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">👥 Gestion des Employés</h1>
                            <p className="text-sm text-gray-500 mt-1">CRUD complet : Créer, Consulter, Modifier, Supprimer</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <ViewSwitcher value={view} onChange={setView} />
                            <button onClick={() => setShowCreateModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm shadow-sm">
                                + Nouvel employé
                            </button>
                        </div>
                    </div>


                    {/* STATS */}
                    <div className="grid grid-cols-3 gap-4">
                        <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-indigo-500">
                            <p className="text-xs text-gray-500 uppercase">Total employés</p>
                            <p className="text-2xl font-bold text-gray-900 mt-1">{stats.total}</p>
                        </div>
                        <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                            <p className="text-xs text-gray-500 uppercase">Actifs</p>
                            <p className="text-2xl font-bold text-green-700 mt-1">{stats.active}</p>
                        </div>
                        <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-400">
                            <p className="text-xs text-gray-500 uppercase">Inactifs</p>
                            <p className="text-2xl font-bold text-gray-600 mt-1">{stats.inactive}</p>
                        </div>
                    </div>

                    {/* FILTRES */}
                    <div className="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                        <div className="flex-1 min-w-[200px]">
                            <label className="block text-xs font-medium text-gray-700 mb-1">Rechercher</label>
                            <input type="text" value={search} onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                                placeholder="Nom, matricule, email..."
                                className="w-full rounded-md border-gray-300 text-sm" />
                        </div>
                        <div className="w-40">
                            <label className="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                            <select value={status} onChange={(e) => setStatus(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Tous</option>
                                {Object.entries(STATUS_CONFIG).map(([k, v]) => <option key={k} value={k}>{v.label}</option>)}
                            </select>
                        </div>
                        <div className="w-40">
                            <label className="block text-xs font-medium text-gray-700 mb-1">Département</label>
                            <select value={department} onChange={(e) => setDepartment(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Tous</option>
                                {departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                            </select>
                        </div>
                        <button onClick={applyFilters} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">Filtrer</button>
                        <button onClick={resetFilters} className="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm py-2 px-4 rounded-md">Réinitialiser</button>
                    </div>

                    {/* TABLE */}
                    {view === 'list' ? (
                        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-gray-50 border-b"><tr>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Matricule</th>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Employé</th>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Contact</th>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Département</th>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Poste</th>
                                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Embauche</th>
                                        <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">Statut</th>
                                        <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                                    </tr></thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {employees.length === 0 ? (
                                            <tr><td colSpan={8} className="p-8 text-center text-gray-500">
                                                Aucun employé. <button onClick={() => setShowCreateModal(true)} className="text-indigo-600 font-medium hover:underline">Créer le premier →</button>
                                            </td></tr>
                                        ) : employees.map((e) => {
                                            const cfg = STATUS_CONFIG[e.status] || STATUS_CONFIG.inactive;
                                            return (
                                                <tr key={e.id} className="hover:bg-gray-50">
                                                    <td className="p-3 font-mono text-xs text-gray-700">{e.matricule}</td>
                                                    <td className="p-3">
                                                        <div className="font-medium text-gray-900">{e.full_name}</div>
                                                        <div className="text-xs text-gray-500">{e.sex === 'M' ? '♂ Homme' : e.sex === 'F' ? '♀ Femme' : '—'}</div>
                                                    </td>
                                                    <td className="p-3 text-xs text-gray-600">
                                                        {e.email && <div>{e.email}</div>}
                                                        {e.phone && <div>{e.phone}</div>}
                                                        {!e.email && !e.phone && '—'}
                                                    </td>
                                                    <td className="p-3 text-xs">{e.department?.name || '—'}</td>
                                                    <td className="p-3 text-xs">{e.position?.name || '—'}</td>
                                                    <td className="p-3 text-xs">{new Date(e.hire_date).toLocaleDateString('fr-FR')}</td>
                                                    <td className="p-3 text-center">
                                                        <span className={'px-2 py-1 rounded-full text-xs font-semibold ' + cfg.color}>{cfg.label}</span>
                                                    </td>
                                                    <td className="p-3 text-right">
                                                        <Link href={route('hr.employees.show', e.id)} className="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-md">
                                                            Voir →
                                                        </Link>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    ) : (
                        <KanbanBoard
                            data={employees}
                            rowKey={(e) => e.id}
                            groupBy={(e) => (STATUS_CONFIG[e.status] ? e.status : 'inactive')}
                            columns={Object.entries(STATUS_CONFIG).map(([key, cfg]) => ({ key, label: cfg.label, colorClass: cfg.color }))}
                            emptyMessage="Aucun employé"
                            renderCard={(e) => {
                                const cfg = STATUS_CONFIG[e.status] || STATUS_CONFIG.inactive;
                                return (
                                    <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <div className="font-medium text-gray-900">{e.full_name}</div>
                                                <div className="font-mono text-xs text-gray-500">{e.matricule}</div>
                                            </div>
                                            <span className={'px-2 py-1 rounded-full text-xs font-semibold ' + cfg.color}>{cfg.label}</span>
                                        </div>
                                        <div className="mt-2 text-xs text-gray-600">
                                            {e.department?.name || '—'} {e.position?.name ? `· ${e.position.name}` : ''}
                                        </div>
                                        <div className="mt-1 text-xs text-gray-500">
                                            {e.email || e.phone || '—'}
                                        </div>
                                        <div className="mt-1 text-xs text-gray-400">
                                            Embauché le {new Date(e.hire_date).toLocaleDateString('fr-FR')}
                                        </div>
                                        <div className="mt-3 flex gap-3 border-t border-gray-100 pt-2 text-xs">
                                            <Link href={route('hr.employees.show', e.id)} className="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-md">
                                                Voir →
                                            </Link>
                                        </div>
                                    </div>
                                );
                            }}
                        />
                    )}
                </div>
            </div>

            {showCreateModal && <EmployeeModal mode="create" departments={departments} onClose={() => setShowCreateModal(false)} />}
        </ErpLayout>
    );
}

/* ===== MODAL CREATE/EDIT ===== */
function EmployeeModal({ mode, employee, departments, onClose }: {
    mode: 'create' | 'edit';
    employee?: any;
    departments: Department[];
    onClose: () => void;
}) {
    const [form, setForm] = useState({
        first_name: employee?.first_name || '',
        last_name: employee?.last_name || '',
        email: employee?.email || '',
        phone: employee?.phone || '',
        sex: employee?.sex || '',
        birth_date: employee?.birth_date || '',
        birth_place: employee?.birth_place || '',
        nationality: employee?.nationality || '',
        id_card_number: employee?.id_card_number || '',
        cnps_number: employee?.cnps_number || '',
        address: employee?.address || '',
        marital_status: employee?.marital_status || '',
        dependents_count: employee?.dependents_count ?? 0,
        hire_date: employee?.hire_date || new Date().toISOString().slice(0, 10),
        department_id: employee?.department?.id || '',
        position_id: employee?.position?.id || '',
        bank_name: employee?.bank_name || '',
        bank_account: employee?.bank_account || '',
        mobile_money: employee?.mobile_money || '',
        payment_method: employee?.payment_method || 'bank',
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true); setErrors({});
        const options = {
            onError: (errs: any) => { setErrors(errs); setProcessing(false); },
            onSuccess: () => onClose(),
            onFinish: () => setProcessing(false),
        };
        if (mode === 'create') {
            router.post(route('hr.employees.store'), form, options);
        } else {
            router.put(route('hr.employees.update', employee.id), form, options);
        }
    };

    const set = (key: string, value: any) => setForm((f: any) => ({ ...f, [key]: value }));

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                <form onSubmit={submit}>
                    <div className="p-6 border-b sticky top-0 bg-white z-10">
                        <h2 className="text-xl font-bold text-gray-900">
                            {mode === 'create' ? '➕ Nouvel employé' : '✏️ Modifier l\'employé'}
                        </h2>
                    </div>
                    <div className="p-6 space-y-6">
                        {/* IDENTITÉ */}
                        <div>
                            <h3 className="font-bold text-gray-800 mb-3 border-b pb-2">👤 Identité</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <Field label="Prénom *" error={errors.first_name}>
                                    <input type="text" value={form.first_name} onChange={(e) => set('first_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required />
                                </Field>
                                <Field label="Nom *" error={errors.last_name}>
                                    <input type="text" value={form.last_name} onChange={(e) => set('last_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required />
                                </Field>
                                <Field label="Sexe">
                                    <select value={form.sex} onChange={(e) => set('sex', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                        <option value="">—</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </Field>
                                <Field label="Date de naissance">
                                    <input type="date" value={form.birth_date} onChange={(e) => set('birth_date', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="Lieu de naissance">
                                    <input type="text" value={form.birth_place} onChange={(e) => set('birth_place', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="Nationalité">
                                    <input type="text" value={form.nationality} onChange={(e) => set('nationality', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" placeholder="Ivoirienne" />
                                </Field>
                                <Field label="N° Pièce d'identité">
                                    <input type="text" value={form.id_card_number} onChange={(e) => set('id_card_number', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="N° CNPS">
                                    <input type="text" value={form.cnps_number} onChange={(e) => set('cnps_number', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                            </div>
                        </div>

                        {/* CONTACT */}
                        <div>
                            <h3 className="font-bold text-gray-800 mb-3 border-b pb-2">📞 Contact</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <Field label="Email" error={errors.email}>
                                    <input type="email" value={form.email} onChange={(e) => set('email', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="Téléphone">
                                    <input type="tel" value={form.phone} onChange={(e) => set('phone', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <div className="col-span-2">
                                    <Field label="Adresse">
                                        <textarea value={form.address} onChange={(e) => set('address', e.target.value)} rows={2} className="w-full rounded-md border-gray-300 text-sm" />
                                    </Field>
                                </div>
                                <Field label="Situation matrimoniale">
                                    <select value={form.marital_status} onChange={(e) => set('marital_status', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                        <option value="">—</option>
                                        <option value="single">Célibataire</option>
                                        <option value="married">Marié(e)</option>
                                        <option value="divorced">Divorcé(e)</option>
                                        <option value="widowed">Veuf(ve)</option>
                                    </select>
                                </Field>
                                <Field label="Nombre d'enfants">
                                    <input type="number" min="0" value={form.dependents_count} onChange={(e) => set('dependents_count', parseInt(e.target.value) || 0)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                            </div>
                        </div>

                        {/* POSTE */}
                        <div>
                            <h3 className="font-bold text-gray-800 mb-3 border-b pb-2">💼 Poste & Embauche</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <Field label="Date d'embauche *" error={errors.hire_date}>
                                    <input type="date" value={form.hire_date} onChange={(e) => set('hire_date', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required />
                                </Field>
                                <Field label="Département">
                                    <select value={form.department_id} onChange={(e) => set('department_id', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                        <option value="">—</option>
                                        {departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                                    </select>
                                </Field>
                            </div>
                        </div>

                        {/* PAIEMENT */}
                        <div>
                            <h3 className="font-bold text-gray-800 mb-3 border-b pb-2">💳 Paiement</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <Field label="Mode de paiement">
                                    <select value={form.payment_method} onChange={(e) => set('payment_method', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                        <option value="bank">Virement bancaire</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="cash">Espèces</option>
                                    </select>
                                </Field>
                                <Field label="Banque">
                                    <input type="text" value={form.bank_name} onChange={(e) => set('bank_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="N° compte bancaire">
                                    <input type="text" value={form.bank_account} onChange={(e) => set('bank_account', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                                <Field label="Mobile Money">
                                    <input type="text" value={form.mobile_money} onChange={(e) => set('mobile_money', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                                </Field>
                            </div>
                        </div>
                    </div>
                    <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg sticky bottom-0">
                        <button type="button" onClick={onClose} disabled={processing} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Annuler</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50">
                            {processing ? '...' : (mode === 'create' ? 'Créer' : 'Enregistrer')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            {children}
            {error && <p className="text-xs text-red-600 mt-1">{error}</p>}
        </div>
    );
}
