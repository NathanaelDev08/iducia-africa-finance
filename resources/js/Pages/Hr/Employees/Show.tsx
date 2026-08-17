import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useRef, useState } from 'react';

interface Contract { id: number; contract_number: string; contract_type: string; start_date: string; end_date: string | null; base_salary: number; status: string; }
interface Document { id: number; document_type: string; name: string; status: string; expires_at: string | null; }
interface Child { id: number; name: string; birth_date: string | null; }

interface EmployeeDetails {
    id: number; matricule: string; first_name: string; last_name: string; full_name: string;
    photo_path: string | null; photo_url: string | null;
    email: string | null; phone: string | null; sex: string | null;
    birth_date: string | null; birth_place: string | null; nationality: string | null;
    id_card_number: string | null; cnps_number: string | null;
    address: string | null; marital_status: string | null; dependents_count: number;
    spouse_name: string | null; spouse_profession: string | null; spouse_employer: string | null;
    hire_date: string; seniority_date: string | null; status: string;
    bank_name: string | null; bank_account: string | null; mobile_money: string | null; payment_method: string | null;
    department: { id: number; name: string } | null;
    position: { id: number; name: string } | null;
    contracts: Contract[];
    documents: Document[];
    children: Child[];
}

interface Props { employee: EmployeeDetails; departments: { id: number; name: string }[]; positions: any[]; contractTypes: any[]; }

const STATUS_CONFIG: Record<string, { label: string; color: string }> = {
    active: { label: 'Actif', color: 'bg-green-100 text-green-800' },
    inactive: { label: 'Inactif', color: 'bg-gray-100 text-gray-800' },
    suspended: { label: 'Suspendu', color: 'bg-yellow-100 text-yellow-800' },
    terminated: { label: 'Terminé', color: 'bg-red-100 text-red-800' },
};

const formatMoney = (v: number) => (v || 0).toLocaleString('fr-FR') + ' FCFA';

export default function Show({ employee, departments, positions, contractTypes }: Props) {
    const [showEditModal, setShowEditModal] = useState(false);
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [confirmAction, setConfirmAction] = useState<{ label: string; route: string; color: string } | null>(null);
    const [uploadingPhoto, setUploadingPhoto] = useState(false);
    const photoInputRef = useRef<HTMLInputElement>(null);

    const cfg = STATUS_CONFIG[employee.status] || STATUS_CONFIG.inactive;
    const currentContract = employee.contracts.find((c) => c.status === 'active');
    const isMarried = employee.marital_status === 'married';

    const executeAction = () => {
        if (!confirmAction) return;
        router.post(confirmAction.route, {}, { onFinish: () => setConfirmAction(null) });
    };

    const handleDelete = () => {
        router.delete(route('hr.employees.destroy', employee.id));
    };

    const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('photo', file);
        setUploadingPhoto(true);
        router.post(route('hr.employees.photo.store', employee.id), fd, {
            forceFormData: true,
            onFinish: () => { setUploadingPhoto(false); if (photoInputRef.current) photoInputRef.current.value = ''; },
        });
    };

    return (
        <ErpLayout>
            <Head title={employee.full_name} />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <Link href={route('hr.employees.index')} className="text-sm text-indigo-600 hover:underline">← Retour à la liste</Link>


                    {/* HEADER */}
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <div className="flex justify-between items-start gap-4">
                            <div className="flex items-start gap-4">
                                <div className="relative group flex-shrink-0">
                                    {employee.photo_url ? (
                                        <img src={employee.photo_url} alt={employee.full_name} className="w-16 h-16 rounded-full object-cover" />
                                    ) : (
                                        <div className="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700">
                                            {employee.first_name[0]}{employee.last_name[0]}
                                        </div>
                                    )}
                                    <button
                                        type="button"
                                        onClick={() => photoInputRef.current?.click()}
                                        disabled={uploadingPhoto}
                                        title="Changer la photo"
                                        className="absolute -bottom-1 -right-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow disabled:opacity-50"
                                    >
                                        {uploadingPhoto ? '…' : '📷'}
                                    </button>
                                    <input ref={photoInputRef} type="file" accept="image/png,image/jpeg,image/jpg,image/webp" className="hidden" onChange={handlePhotoChange} />
                                </div>
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900">{employee.full_name}</h1>
                                    <div className="flex items-center gap-2 mt-1">
                                        <span className="text-sm text-gray-500 font-mono">{employee.matricule}</span>
                                        <span className={'px-2 py-0.5 rounded-full text-xs font-semibold ' + cfg.color}>{cfg.label}</span>
                                    </div>
                                    <button type="button" onClick={() => photoInputRef.current?.click()} disabled={uploadingPhoto} className="text-xs text-indigo-600 hover:underline mt-1 disabled:opacity-50">
                                        {uploadingPhoto ? 'Envoi…' : 'Changer la photo'}
                                    </button>
                                    <div className="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                                        {employee.department && <span>🏢 {employee.department.name}</span>}
                                        {employee.position && <span>💼 {employee.position.name}</span>}
                                        <span>📅 Embauché le {new Date(employee.hire_date).toLocaleDateString('fr-FR')}</span>
                                    </div>
                                </div>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <button onClick={() => setShowEditModal(true)} className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">✏️ Modifier</button>
                                {employee.status === 'active' ? (
                                    <button onClick={() => setConfirmAction({ label: 'Désactiver cet employé', route: route('hr.employees.deactivate', employee.id), color: 'yellow' })}
                                        className="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-md text-sm">⏸ Désactiver</button>
                                ) : (
                                    <button onClick={() => setConfirmAction({ label: 'Réactiver cet employé', route: route('hr.employees.activate', employee.id), color: 'green' })}
                                        className="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">▶ Réactiver</button>
                                )}
                                <button onClick={() => setConfirmDelete(true)} className="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md text-sm">🗑 Supprimer</button>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* INFOS PERSO */}
                        <div className="bg-white rounded-lg shadow-sm p-6">
                            <h3 className="font-bold text-gray-800 mb-4">👤 Informations personnelles</h3>
                            <dl className="space-y-2 text-sm">
                                <InfoRow label="Sexe" value={employee.sex === 'M' ? 'Masculin' : employee.sex === 'F' ? 'Féminin' : '—'} />
                                <InfoRow label="Naissance" value={employee.birth_date ? new Date(employee.birth_date).toLocaleDateString('fr-FR') : '—'} />
                                <InfoRow label="Lieu" value={employee.birth_place || '—'} />
                                <InfoRow label="Nationalité" value={employee.nationality || '—'} />
                                <InfoRow label="CNI" value={employee.id_card_number || '—'} />
                                <InfoRow label="CNPS" value={employee.cnps_number || '—'} />
                                <InfoRow label="Situation" value={employee.marital_status || '—'} />
                                <InfoRow label="Enfants (nb.)" value={String(employee.dependents_count)} />
                            </dl>
                            {isMarried && employee.spouse_name && (
                                <>
                                    <h4 className="font-semibold text-gray-700 mt-4 mb-2 text-xs uppercase">Conjoint(e)</h4>
                                    <dl className="space-y-2 text-sm">
                                        <InfoRow label="Nom" value={employee.spouse_name} />
                                        <InfoRow label="Profession" value={employee.spouse_profession || '—'} />
                                        <InfoRow label="Employeur" value={employee.spouse_employer || '—'} />
                                    </dl>
                                </>
                            )}
                        </div>

                        {/* CONTACT */}
                        <div className="bg-white rounded-lg shadow-sm p-6">
                            <h3 className="font-bold text-gray-800 mb-4">📞 Contact</h3>
                            <dl className="space-y-2 text-sm">
                                <InfoRow label="Email" value={employee.email || '—'} />
                                <InfoRow label="Téléphone" value={employee.phone || '—'} />
                                <InfoRow label="Adresse" value={employee.address || '—'} />
                            </dl>
                            <h3 className="font-bold text-gray-800 mb-4 mt-6">💳 Paiement</h3>
                            <dl className="space-y-2 text-sm">
                                <InfoRow label="Mode" value={employee.payment_method || '—'} />
                                <InfoRow label="Banque" value={employee.bank_name || '—'} />
                                <InfoRow label="Compte" value={employee.bank_account || '—'} />
                                <InfoRow label="Mobile Money" value={employee.mobile_money || '—'} />
                            </dl>
                        </div>

                        {/* CONTRAT ACTIF */}
                        <div className="bg-white rounded-lg shadow-sm p-6">
                            <h3 className="font-bold text-gray-800 mb-4">💼 Contrat actif</h3>
                            {currentContract ? (
                                <dl className="space-y-2 text-sm">
                                    <InfoRow label="Type" value={currentContract.contract_type} />
                                    <InfoRow label="N° contrat" value={currentContract.contract_number || '—'} />
                                    <InfoRow label="Début" value={new Date(currentContract.start_date).toLocaleDateString('fr-FR')} />
                                    <InfoRow label="Fin" value={currentContract.end_date ? new Date(currentContract.end_date).toLocaleDateString('fr-FR') : 'CDI'} />
                                    <InfoRow label="Salaire de base" value={formatMoney(currentContract.base_salary)} highlight />
                                </dl>
                            ) : (
                                <p className="text-sm text-gray-500">Aucun contrat actif.</p>
                            )}
                            <p className="text-xs text-gray-500 mt-4">{employee.contracts.length} contrat(s) au total</p>
                        </div>
                    </div>

                    {/* DOCUMENTS */}
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <h3 className="font-bold text-gray-800 mb-4">📎 Documents ({employee.documents.length})</h3>
                        {employee.documents.length === 0 ? (
                            <p className="text-sm text-gray-500">Aucun document enregistré.</p>
                        ) : (
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 border-b"><tr>
                                    <th className="p-2 text-left text-xs text-gray-600 uppercase">Type</th>
                                    <th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
                                    <th className="p-2 text-left text-xs text-gray-600 uppercase">Statut</th>
                                    <th className="p-2 text-left text-xs text-gray-600 uppercase">Expiration</th>
                                </tr></thead>
                                <tbody className="divide-y">
                                    {employee.documents.map((d) => (
                                        <tr key={d.id}>
                                            <td className="p-2 font-mono text-xs">{d.document_type}</td>
                                            <td className="p-2">{d.name}</td>
                                            <td className="p-2"><span className={'px-2 py-0.5 rounded text-xs font-semibold ' + (d.status === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')}>{d.status}</span></td>
                                            <td className="p-2 text-xs">{d.expires_at ? new Date(d.expires_at).toLocaleDateString('fr-FR') : '—'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* ENFANTS */}
                    <EmployeeChildrenSection employeeId={employee.id} items={employee.children} />
                </div>
            </div>

            {showEditModal && (
                <EmployeeEditModal
                    employee={employee}
                    departments={departments}
                    onClose={() => setShowEditModal(false)}
                />
            )}

            {/* MODAL CONFIRM ACTION */}
            {confirmAction && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
                        <div className="p-6">
                            <h2 className="text-lg font-bold text-gray-900">Confirmer</h2>
                            <p className="mt-2 text-sm text-gray-600">Êtes-vous sûr de vouloir <strong>{confirmAction.label}</strong> ?</p>
                        </div>
                        <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                            <button onClick={() => setConfirmAction(null)} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
                            <button onClick={executeAction} className={`px-4 py-2 text-sm text-white rounded-md bg-${confirmAction.color}-600`}>Confirmer</button>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL CONFIRM DELETE */}
            {confirmDelete && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
                        <div className="p-6">
                            <h2 className="text-lg font-bold text-red-700">🗑 Supprimer cet employé ?</h2>
                            <p className="mt-2 text-sm text-gray-600">
                                L'employé <strong>{employee.full_name}</strong> sera supprimé. Cette action ne peut être annulée.
                            </p>
                            {employee.status === 'active' && (
                                <p className="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
                                    ⚠️ Vous devez d'abord désactiver l'employé avant de pouvoir le supprimer.
                                </p>
                            )}
                        </div>
                        <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                            <button onClick={() => setConfirmDelete(false)} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
                            <button onClick={handleDelete} disabled={employee.status === 'active'} className="px-4 py-2 text-sm text-white rounded-md bg-red-600 disabled:opacity-50">Supprimer</button>
                        </div>
                    </div>
                </div>
            )}
        </ErpLayout>
    );
}

function InfoRow({ label, value, highlight = false }: { label: string; value: string; highlight?: boolean }) {
    return (
        <div className="flex justify-between gap-2">
            <dt className="text-gray-500">{label}</dt>
            <dd className={'text-right ' + (highlight ? 'font-bold text-green-700' : 'text-gray-900')}>{value}</dd>
        </div>
    );
}

/* ===== ENFANTS (CRUD inline) ===== */
function EmployeeChildrenSection({ employeeId, items }: { employeeId: number; items: Child[] }) {
    const [name, setName] = useState('');
    const [birthDate, setBirthDate] = useState('');
    const [processing, setProcessing] = useState(false);
    const [confirmDeleteId, setConfirmDeleteId] = useState<number | null>(null);

    const addChild = (e: React.FormEvent) => {
        e.preventDefault();
        if (!name.trim()) return;
        setProcessing(true);
        router.post(route('hr.children.store'), { employee_id: employeeId, name, birth_date: birthDate || null }, {
            preserveScroll: true,
            onSuccess: () => { setName(''); setBirthDate(''); },
            onFinish: () => setProcessing(false),
        });
    };

    const removeChild = (id: number) => {
        router.delete(route('hr.children.destroy', id), { preserveScroll: true, onFinish: () => setConfirmDeleteId(null) });
    };

    return (
        <div className="bg-white rounded-lg shadow-sm p-6">
            <h3 className="font-bold text-gray-800 mb-4">👶 Enfants ({items.length})</h3>
            {items.length === 0 ? (
                <p className="text-sm text-gray-500 mb-4">Aucun enfant enregistré.</p>
            ) : (
                <table className="w-full text-sm mb-4">
                    <thead className="bg-gray-50 border-b"><tr>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Naissance</th>
                        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th>
                    </tr></thead>
                    <tbody className="divide-y">
                        {items.map((c) => (
                            <tr key={c.id}>
                                <td className="p-2">{c.name}</td>
                                <td className="p-2 text-xs">{c.birth_date ? new Date(c.birth_date).toLocaleDateString('fr-FR') : '—'}</td>
                                <td className="p-2 text-right">
                                    <button onClick={() => setConfirmDeleteId(c.id)} className="text-red-600 hover:underline text-xs">🗑 Supprimer</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
            <form onSubmit={addChild} className="flex flex-wrap items-end gap-3">
                <div className="flex-1 min-w-[160px]">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Nom de l'enfant *</label>
                    <input type="text" value={name} onChange={(e) => setName(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required />
                </div>
                <div className="min-w-[160px]">
                    <label className="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    <input type="date" value={birthDate} onChange={(e) => setBirthDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                </div>
                <button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md disabled:opacity-50">
                    {processing ? '...' : '+ Ajouter un enfant'}
                </button>
            </form>

            {confirmDeleteId !== null && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
                        <div className="p-6">
                            <h2 className="text-lg font-bold text-red-700">🗑 Supprimer cet enfant ?</h2>
                            <p className="mt-2 text-sm text-gray-600">Cette action ne peut être annulée.</p>
                        </div>
                        <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                            <button onClick={() => setConfirmDeleteId(null)} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
                            <button onClick={() => removeChild(confirmDeleteId)} className="px-4 py-2 text-sm text-white rounded-md bg-red-600">Supprimer</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

/* ===== MODAL EDIT (réutilise la logique de création) ===== */
function EmployeeEditModal({ employee, departments, onClose }: { employee: any; departments: any[]; onClose: () => void }) {
    const [form, setForm] = useState({
        first_name: employee.first_name,
        last_name: employee.last_name,
        email: employee.email || '',
        phone: employee.phone || '',
        sex: employee.sex || '',
        birth_date: employee.birth_date || '',
        birth_place: employee.birth_place || '',
        nationality: employee.nationality || '',
        id_card_number: employee.id_card_number || '',
        cnps_number: employee.cnps_number || '',
        address: employee.address || '',
        marital_status: employee.marital_status || '',
        dependents_count: employee.dependents_count ?? 0,
        spouse_name: employee.spouse_name || '',
        spouse_profession: employee.spouse_profession || '',
        spouse_employer: employee.spouse_employer || '',
        hire_date: employee.hire_date,
        department_id: employee.department?.id || '',
        position_id: employee.position?.id || '',
        bank_name: employee.bank_name || '',
        bank_account: employee.bank_account || '',
        mobile_money: employee.mobile_money || '',
        payment_method: employee.payment_method || 'bank',
        status: employee.status,
    });
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true); setErrors({});
        router.put(route('hr.employees.update', employee.id), form, {
            onError: (errs: any) => { setErrors(errs); setProcessing(false); },
            onSuccess: () => onClose(),
            onFinish: () => setProcessing(false),
        });
    };
    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                <form onSubmit={submit}>
                    <div className="p-6 border-b sticky top-0 bg-white z-10">
                        <h2 className="text-xl font-bold text-gray-900">✏️ Modifier {employee.full_name}</h2>
                    </div>
                    <div className="p-6 space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="Prénom *" error={errors.first_name}><input type="text" value={form.first_name} onChange={(e) => set('first_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required /></Field>
                            <Field label="Nom *" error={errors.last_name}><input type="text" value={form.last_name} onChange={(e) => set('last_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required /></Field>
                            <Field label="Email"><input type="email" value={form.email} onChange={(e) => set('email', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                            <Field label="Téléphone"><input type="tel" value={form.phone} onChange={(e) => set('phone', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                            <Field label="Sexe"><select value={form.sex} onChange={(e) => set('sex', e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option><option value="M">M</option><option value="F">F</option></select></Field>
                            <Field label="Date naissance"><input type="date" value={form.birth_date} onChange={(e) => set('birth_date', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                            <Field label="Embauche *" error={errors.hire_date}><input type="date" value={form.hire_date} onChange={(e) => set('hire_date', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required /></Field>
                            <Field label="Département"><select value={form.department_id} onChange={(e) => set('department_id', e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{departments.map((d: any) => <option key={d.id} value={d.id}>{d.name}</option>)}</select></Field>
                            <Field label="Statut"><select value={form.status} onChange={(e) => set('status', e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="active">Actif</option><option value="inactive">Inactif</option><option value="suspended">Suspendu</option><option value="terminated">Terminé</option></select></Field>
                            <Field label="Situation matrimoniale" error={errors.marital_status}>
                                <select value={form.marital_status} onChange={(e) => set('marital_status', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                                    <option value="">—</option>
                                    <option value="single">Célibataire</option>
                                    <option value="married">Marié(e)</option>
                                    <option value="divorced">Divorcé(e)</option>
                                    <option value="widowed">Veuf/Veuve</option>
                                </select>
                            </Field>
                            <Field label="Nombre d'enfants (dépendants)"><input type="number" min={0} value={form.dependents_count} onChange={(e) => set('dependents_count', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                        </div>

                        {form.marital_status === 'married' && (
                            <div>
                                <h3 className="text-sm font-semibold text-gray-700 mb-2">Conjoint(e)</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    <Field label="Nom du/de la conjoint(e)" error={errors.spouse_name}><input type="text" value={form.spouse_name} onChange={(e) => set('spouse_name', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                                    <Field label="Profession"><input type="text" value={form.spouse_profession} onChange={(e) => set('spouse_profession', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                                    <Field label="Employeur"><input type="text" value={form.spouse_employer} onChange={(e) => set('spouse_employer', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></Field>
                                </div>
                            </div>
                        )}
                    </div>
                    <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg sticky bottom-0">
                        <button type="button" onClick={onClose} disabled={processing} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md disabled:opacity-50">{processing ? '...' : 'Enregistrer'}</button>
                    </div>
                </form>
            </div>
        </div>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return <div><label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>{children}{error && <p className="text-xs text-red-600 mt-1">{error}</p>}</div>;
}
