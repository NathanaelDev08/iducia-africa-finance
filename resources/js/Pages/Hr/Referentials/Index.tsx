import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Department { id: number; code: string; name: string; is_active: boolean; positions_count: number; employees_count: number; }
interface Position { id: number; code: string; name: string; is_active: boolean; department: { id: number; name: string } | null; employees_count: number; }
interface ContractType { id: number; code: string; name: string; is_active: boolean; }

interface Props {
    departments: Department[];
    positions: Position[];
    contractTypes: ContractType[];
    allDepartments: { id: number; name: string }[];
}

type TabKey = 'departments' | 'positions' | 'contractTypes';

export default function Index({ departments, positions, contractTypes, allDepartments }: Props) {
    const [activeTab, setActiveTab] = useState<TabKey>('departments');
    const flash = (usePage().props as any).flash;

    const tabs = [
        { key: 'departments' as TabKey, label: 'Départements', icon: '🏢' },
        { key: 'positions' as TabKey, label: 'Postes', icon: '💼' },
        { key: 'contractTypes' as TabKey, label: 'Types de contrat', icon: '📝' },
    ];

    return (
        <ErpLayout>
            <Head title="Référentiels RH" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">🗂️ Référentiels RH</h1>
                            <p className="text-sm text-gray-500 mt-1">CRUD : Départements, Postes, Types de contrat</p>
                        </div>
                        <Link href={route('hr.employees.index')} className="text-sm text-indigo-600 hover:underline">← Employés</Link>
                    </div>

                    {flash?.success && <div className="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
                    {flash?.error && <div className="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}

                    <div className="bg-white rounded-t-lg shadow-sm border-b border-gray-200">
                        <nav className="flex">
                            {tabs.map((tab) => {
                                const isActive = activeTab === tab.key;
                                return (
                                    <button key={tab.key} onClick={() => setActiveTab(tab.key)}
                                        className={'relative flex-1 py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 transition ' + (isActive ? 'text-gray-900 font-semibold' : 'text-gray-500')}>
                                        <span className="mr-2">{tab.icon}</span>{tab.label}
                                        <span className={'absolute inset-x-0 bottom-0 h-0.5 ' + (isActive ? 'bg-indigo-600' : 'bg-transparent')} />
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="bg-white rounded-b-lg shadow-sm p-6">
                        {activeTab === 'departments' && <DepartmentsTab data={departments} />}
                        {activeTab === 'positions' && <PositionsTab data={positions} allDepartments={allDepartments} />}
                        {activeTab === 'contractTypes' && <ContractTypesTab data={contractTypes} />}
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}

/* ===== DÉPARTEMENTS ===== */
function DepartmentsTab({ data }: { data: Department[] }) {
    const [modal, setModal] = useState<{ mode: 'create' } | { mode: 'edit'; item: Department } | null>(null);
    const [confirmDelete, setConfirmDelete] = useState<Department | null>(null);

    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button>
            </div>
            <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b"><tr>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
                    <th className="p-3 text-center text-xs text-gray-600 uppercase">Postes</th>
                    <th className="p-3 text-center text-xs text-gray-600 uppercase">Employés</th>
                    <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody className="divide-y">
                    {data.map((d) => (
                        <tr key={d.id} className="hover:bg-gray-50">
                            <td className="p-3 font-mono text-xs">{d.code}</td>
                            <td className="p-3 font-medium">{d.name}</td>
                            <td className="p-3 text-center">{d.positions_count}</td>
                            <td className="p-3 text-center">{d.employees_count}</td>
                            <td className="p-3 text-right">
                                <button onClick={() => setModal({ mode: 'edit', item: d })} className="text-blue-600 hover:underline text-xs mr-3">✏️ Modifier</button>
                                <button onClick={() => setConfirmDelete(d)} className="text-red-600 hover:underline text-xs">🗑 Supprimer</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            {modal && <DepartmentModal mode={modal.mode} item={modal.mode === 'edit' ? modal.item : undefined} onClose={() => setModal(null)} />}
            {confirmDelete && <ConfirmDelete label={confirmDelete.name} onClose={() => setConfirmDelete(null)} onConfirm={() => router.delete(route('hr.referentials.departments.destroy', confirmDelete.id))} />}
        </div>
    );
}

function DepartmentModal({ mode, item, onClose }: { mode: 'create' | 'edit'; item?: Department; onClose: () => void }) {
    const [code, setCode] = useState(item?.code || '');
    const [name, setName] = useState(item?.name || '');
    const [isActive, setIsActive] = useState(item?.is_active ?? true);
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = { code, name, is_active: isActive };
        if (mode === 'create') router.post(route('hr.referentials.departments.store'), payload, { onSuccess: () => onClose() });
        else router.put(route('hr.referentials.departments.update', item!.id), payload, { onSuccess: () => onClose() });
    };
    return (
        <ModalShell title={mode === 'create' ? '➕ Nouveau département' : '✏️ Modifier le département'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-4">
                <Input label="Code *" value={code} onChange={setCode} required />
                <Input label="Nom *" value={name} onChange={setName} required />
                {mode === 'edit' && <Checkbox label="Actif" checked={isActive} onChange={setIsActive} />}
                <ModalFooter onClose={onClose} />
            </form>
        </ModalShell>
    );
}

/* ===== POSTES ===== */
function PositionsTab({ data, allDepartments }: { data: Position[]; allDepartments: { id: number; name: string }[] }) {
    const [modal, setModal] = useState<{ mode: 'create' } | { mode: 'edit'; item: Position } | null>(null);
    const [confirmDelete, setConfirmDelete] = useState<Position | null>(null);
    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button>
            </div>
            <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b"><tr>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Département</th>
                    <th className="p-3 text-center text-xs text-gray-600 uppercase">Employés</th>
                    <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody className="divide-y">
                    {data.map((p) => (
                        <tr key={p.id} className="hover:bg-gray-50">
                            <td className="p-3 font-mono text-xs">{p.code}</td>
                            <td className="p-3 font-medium">{p.name}</td>
                            <td className="p-3 text-xs">{p.department?.name || '—'}</td>
                            <td className="p-3 text-center">{p.employees_count}</td>
                            <td className="p-3 text-right">
                                <button onClick={() => setModal({ mode: 'edit', item: p })} className="text-blue-600 hover:underline text-xs mr-3">✏️ Modifier</button>
                                <button onClick={() => setConfirmDelete(p)} className="text-red-600 hover:underline text-xs">🗑 Supprimer</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            {modal && <PositionModal mode={modal.mode} item={modal.mode === 'edit' ? modal.item : undefined} allDepartments={allDepartments} onClose={() => setModal(null)} />}
            {confirmDelete && <ConfirmDelete label={confirmDelete.name} onClose={() => setConfirmDelete(null)} onConfirm={() => router.delete(route('hr.referentials.positions.destroy', confirmDelete.id))} />}
        </div>
    );
}

function PositionModal({ mode, item, allDepartments, onClose }: { mode: 'create' | 'edit'; item?: Position; allDepartments: { id: number; name: string }[]; onClose: () => void }) {
    const [code, setCode] = useState(item?.code || '');
    const [name, setName] = useState(item?.name || '');
    const [departmentId, setDepartmentId] = useState(item?.department?.id || '');
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = { code, name, department_id: departmentId || null };
        if (mode === 'create') router.post(route('hr.referentials.positions.store'), payload, { onSuccess: () => onClose() });
        else router.put(route('hr.referentials.positions.update', item!.id), payload, { onSuccess: () => onClose() });
    };
    return (
        <ModalShell title={mode === 'create' ? '➕ Nouveau poste' : '✏️ Modifier le poste'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-4">
                <Input label="Code *" value={code} onChange={setCode} required />
                <Input label="Nom *" value={name} onChange={setName} required />
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Département</label>
                    <select value={departmentId} onChange={(e) => setDepartmentId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                        <option value="">—</option>
                        {allDepartments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                    </select>
                </div>
                <ModalFooter onClose={onClose} />
            </form>
        </ModalShell>
    );
}

/* ===== TYPES DE CONTRAT ===== */
function ContractTypesTab({ data }: { data: ContractType[] }) {
    const [modal, setModal] = useState<{ mode: 'create' } | { mode: 'edit'; item: ContractType } | null>(null);
    const [confirmDelete, setConfirmDelete] = useState<ContractType | null>(null);
    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button>
            </div>
            <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b"><tr>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th>
                    <th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
                    <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody className="divide-y">
                    {data.map((c) => (
                        <tr key={c.id} className="hover:bg-gray-50">
                            <td className="p-3 font-mono text-xs">{c.code}</td>
                            <td className="p-3 font-medium">{c.name}</td>
                            <td className="p-3 text-right">
                                <button onClick={() => setModal({ mode: 'edit', item: c })} className="text-blue-600 hover:underline text-xs mr-3">✏️ Modifier</button>
                                <button onClick={() => setConfirmDelete(c)} className="text-red-600 hover:underline text-xs">🗑 Supprimer</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            {modal && <ContractTypeModal mode={modal.mode} item={modal.mode === 'edit' ? modal.item : undefined} onClose={() => setModal(null)} />}
            {confirmDelete && <ConfirmDelete label={confirmDelete.name} onClose={() => setConfirmDelete(null)} onConfirm={() => router.delete(route('hr.referentials.contract-types.destroy', confirmDelete.id))} />}
        </div>
    );
}

function ContractTypeModal({ mode, item, onClose }: { mode: 'create' | 'edit'; item?: ContractType; onClose: () => void }) {
    const [code, setCode] = useState(item?.code || '');
    const [name, setName] = useState(item?.name || '');
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = { code, name };
        if (mode === 'create') router.post(route('hr.referentials.contract-types.store'), payload, { onSuccess: () => onClose() });
        else router.put(route('hr.referentials.contract-types.update', item!.id), payload, { onSuccess: () => onClose() });
    };
    return (
        <ModalShell title={mode === 'create' ? '➕ Nouveau type' : '✏️ Modifier le type'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-4">
                <Input label="Code *" value={code} onChange={setCode} required />
                <Input label="Nom *" value={name} onChange={setName} required />
                <ModalFooter onClose={onClose} />
            </form>
        </ModalShell>
    );
}

/* ===== COMPOSANTS GÉNÉRIQUES ===== */
function ModalShell({ title, children, onClose }: { title: string; children: React.ReactNode; onClose: () => void }) {
    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white rounded-lg shadow-2xl max-w-md w-full" onClick={(e) => e.stopPropagation()}>
                <div className="p-5 border-b"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
                <div className="p-5">{children}</div>
            </div>
        </div>
    );
}

function Input({ label, value, onChange, required }: { label: string; value: string; onChange: (v: string) => void; required?: boolean }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            <input type="text" value={value} onChange={(e) => onChange(e.target.value)} required={required} className="w-full rounded-md border-gray-300 text-sm" />
        </div>
    );
}

function Checkbox({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
    return (
        <label className="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} className="rounded border-gray-300" />
            {label}
        </label>
    );
}

function ModalFooter({ onClose }: { onClose: () => void }) {
    return (
        <div className="flex justify-end gap-3 pt-2">
            <button type="button" onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
            <button type="submit" className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Enregistrer</button>
        </div>
    );
}

function ConfirmDelete({ label, onClose, onConfirm }: { label: string; onClose: () => void; onConfirm: () => void }) {
    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
                <div className="p-6">
                    <h2 className="text-lg font-bold text-red-700">🗑 Supprimer ?</h2>
                    <p className="mt-2 text-sm text-gray-600">Confirmer la suppression de <strong>{label}</strong> ?</p>
                </div>
                <div className="p-5 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                    <button onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
                    <button onClick={onConfirm} className="px-4 py-2 text-sm text-white bg-red-600 rounded-md">Supprimer</button>
                </div>
            </div>
        </div>
    );
}
