import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, ReactNode, useEffect, useState } from 'react';
import { FilePlus2, Pencil, Plus, Trash2 } from 'lucide-react';

interface TaxRow { id: number; code: string; name: string; type: string; is_active: boolean; rate: number | null; effective_from: string | null; rates_count: number; }
interface DeclarationRow { id: number; tax_name: string; year: number | null; month: number | null; base_amount: number; tax_amount: number; status: string; }
interface DeadlineRow { id: number; name: string; deadline_date: string | null; status: string; }

interface Props { taxes: TaxRow[]; declarations: DeclarationRow[]; deadlines: DeadlineRow[]; initialTab: string; }

type TabKey = 'taxes' | 'declarations' | 'echeances';

const typeLabels: Record<string, string> = {
    vat: 'TVA', withholding: 'Retenue à la source', income_tax: 'Impôt sur le revenu', ts: 'Taxe sur salaires', other: 'Autre',
};

const statusCfg: Record<string, { label: string; cls: string }> = {
    pending: { label: 'À déclarer', cls: 'bg-amber-100 text-amber-800' },
    filed: { label: 'Déposée', cls: 'bg-blue-100 text-blue-800' },
    paid: { label: 'Payée', cls: 'bg-green-100 text-green-800' },
    late: { label: 'En retard', cls: 'bg-red-100 text-red-800' },
    done: { label: 'Faite', cls: 'bg-green-100 text-green-800' },
};

const fmt = (v: number) => (v || 0).toLocaleString('fr-FR');

export default function Index({ taxes, declarations, deadlines, initialTab }: Props) {
    const [activeTab, setActiveTab] = useState<TabKey>((initialTab as TabKey) || 'taxes');
    const flash: any = (usePage().props as any).flash;

    useEffect(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', activeTab);
        window.history.replaceState({}, '', url.toString());
    }, [activeTab]);

    const tabs: { key: TabKey; label: string; icon: string }[] = [
        { key: 'taxes', label: 'Taxes & taux', icon: '🧾' },
        { key: 'declarations', label: 'Déclarations', icon: '📋' },
        { key: 'echeances', label: 'Échéances', icon: '⏰' },
    ];

    return (
        <ErpLayout>
            <Head title="Fiscalité" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">🏛️ Fiscalité</h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">Taxes, déclarations et échéances fiscales de l'entreprise active</p>
                    </div>

                    {flash?.success && <div className="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
                    {flash?.error && <div className="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}

                    <div className="bg-white dark:bg-gray-800 rounded-t-lg shadow-sm border-b border-gray-200 dark:border-gray-700">
                        <nav className="flex overflow-x-auto" aria-label="Tabs">
                            {tabs.map((tab) => {
                                const isActive = activeTab === tab.key;
                                return (
                                    <button
                                        key={tab.key}
                                        onClick={() => setActiveTab(tab.key)}
                                        className={`px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 ${
                                            isActive
                                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                                        }`}
                                    >
                                        {tab.icon} {tab.label}
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-b-lg shadow-sm p-6">
                        {activeTab === 'taxes' && <TaxesTab taxes={taxes} />}
                        {activeTab === 'declarations' && <DeclarationsTab declarations={declarations} />}
                        {activeTab === 'echeances' && <EcheancesTab deadlines={deadlines} />}
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}

/* ═══════════ ONGLET TAXES ═══════════ */
function TaxesTab({ taxes }: { taxes: TaxRow[] }) {
    const [modal, setModal] = useState<null | { mode: 'create' } | { mode: 'edit'; tax: TaxRow }>(null);
    const [rateModal, setRateModal] = useState<TaxRow | null>(null);

    const remove = (t: TaxRow) => {
        if (window.confirm(`Supprimer la taxe ${t.code} ?`)) router.delete(route('tax.taxes.destroy', t.id));
    };

    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md flex items-center gap-1">
                    <Plus className="h-4 w-4" /> Ajouter une taxe
                </button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Code</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Nom</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Type</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Taux actuel</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Date d'effet</th>
                            <th className="p-3 text-center text-xs text-gray-600 dark:text-gray-300 uppercase">Historique</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {taxes.length === 0 && <tr><td colSpan={7} className="p-6 text-center text-gray-400">Aucune taxe</td></tr>}
                        {taxes.map((t) => (
                            <tr key={t.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td className="p-3 font-mono font-semibold">{t.code}</td>
                                <td className="p-3">{t.name}</td>
                                <td className="p-3"><span className="px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">{typeLabels[t.type] || t.type}</span></td>
                                <td className="p-3 text-right font-mono font-bold">{t.rate !== null ? t.rate.toLocaleString('fr-FR') + ' %' : '—'}</td>
                                <td className="p-3">{t.effective_from || '—'}</td>
                                <td className="p-3 text-center"><span className="text-xs text-gray-500">{t.rates_count} taux</span></td>
                                <td className="p-3 text-right whitespace-nowrap">
                                    <button onClick={() => setRateModal(t)} className="text-green-600 hover:text-green-800 text-xs font-medium mr-3" title="Nouveau taux">+ Taux</button>
                                    <button onClick={() => setModal({ mode: 'edit', tax: t })} className="text-indigo-600 hover:text-indigo-800 mr-3" title="Modifier"><Pencil className="h-4 w-4 inline" /></button>
                                    <button onClick={() => remove(t)} className="text-red-600 hover:text-red-800" title="Supprimer"><Trash2 className="h-4 w-4 inline" /></button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <p className="text-xs text-gray-500 mt-3">Les taux sont versionnés avec dates d'effet (conformité moteur de règles) : un changement de taux ne modifie jamais les anciens calculs.</p>

            {modal && <TaxModal mode={modal.mode} tax={modal.mode === 'edit' ? modal.tax : undefined} onClose={() => setModal(null)} />}
            {rateModal && <RateModal tax={rateModal} onClose={() => setRateModal(null)} />}
        </div>
    );
}

function TaxModal({ mode, tax, onClose }: { mode: 'create' | 'edit'; tax?: TaxRow; onClose: () => void }) {
    const [form, setForm] = useState({
        code: tax?.code || '',
        name: tax?.name || '',
        type: tax?.type || 'vat',
        rate: tax?.rate ?? 18,
        effective_from: new Date().toISOString().split('T')[0],
    });

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (mode === 'create') router.post(route('tax.taxes.store'), form, { onSuccess: onClose });
        else router.put(route('tax.taxes.update', tax!.id), { name: form.name, type: form.type, is_active: true }, { onSuccess: onClose });
    };

    return (
        <Modal title={mode === 'create' ? '➕ Ajouter une taxe' : '✏️ Modifier la taxe'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                {mode === 'create' && (
                    <Field label="Code *">
                        <input value={form.code} onChange={(e) => set('code', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" placeholder="TVA" required />
                    </Field>
                )}
                <Field label="Nom *">
                    <input value={form.name} onChange={(e) => set('name', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <div className="grid grid-cols-2 gap-3">
                    <Field label="Type *">
                        <select value={form.type} onChange={(e) => set('type', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            <option value="vat">TVA</option>
                            <option value="withholding">Retenue à la source</option>
                            <option value="income_tax">Impôt sur le revenu</option>
                            <option value="ts">Taxe sur salaires</option>
                            <option value="other">Autre</option>
                        </select>
                    </Field>
                    {mode === 'create' && (
                        <Field label="Taux initial (%)">
                            <input type="number" step="0.01" min="0" max="100" value={form.rate} onChange={(e) => set('rate', parseFloat(e.target.value) || 0)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                        </Field>
                    )}
                </div>
                {mode === 'create' && (
                    <Field label="Date d'effet">
                        <input type="date" value={form.effective_from} onChange={(e) => set('effective_from', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                    </Field>
                )}
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Enregistrer</button>
                </div>
            </form>
        </Modal>
    );
}

function RateModal({ tax, onClose }: { tax: TaxRow; onClose: () => void }) {
    const [form, setForm] = useState({ rate: tax.rate ?? 0, effective_from: new Date().toISOString().split('T')[0] });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        router.post(route('tax.rates.store', tax.id), form, { onSuccess: onClose });
    };

    return (
        <Modal title={'📈 Nouveau taux : ' + tax.name} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                <Field label="Nouveau taux (%) *">
                    <input type="number" step="0.01" min="0" max="100" value={form.rate} onChange={(e) => setForm((f) => ({ ...f, rate: parseFloat(e.target.value) || 0 }))} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <Field label="Date d'effet *">
                    <input type="date" value={form.effective_from} onChange={(e) => setForm((f) => ({ ...f, effective_from: e.target.value }))} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white">Ajouter le taux</button>
                </div>
            </form>
        </Modal>
    );
}

/* ═══════════ ONGLET DÉCLARATIONS ═══════════ */
function DeclarationsTab({ declarations }: { declarations: DeclarationRow[] }) {
    const [period, setPeriod] = useState(new Date().toISOString().slice(0, 7));

    const generate = (e: FormEvent) => {
        e.preventDefault();
        const [y, m] = period.split('-').map(Number);
        router.post(route('tax.declarations.generate'), { year: y, month: m });
    };

    const setStatus = (id: number, status: string) => router.put(route('tax.declarations.status', id), { status });

    const periodLabel = (d: DeclarationRow) => d.month && d.year ? String(d.month).padStart(2, '0') + '/' + d.year : '—';

    return (
        <div>
            <form onSubmit={generate} className="flex flex-wrap items-end gap-3 mb-5 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <Field label="Période à déclarer">
                    <input type="month" value={period} onChange={(e) => setPeriod(e.target.value)} className="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                </Field>
                <button type="submit" className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md flex items-center gap-1">
                    <FilePlus2 className="h-4 w-4" /> Générer les déclarations
                </button>
                <p className="text-xs text-gray-500 w-full">TVA calculée sur les ventes du mois (comptes 7) • Taxe sur salaires calculée sur la masse salariale (paie du mois).</p>
            </form>

            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Période</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Déclaration</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Base</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Montant dû</th>
                            <th className="p-3 text-center text-xs text-gray-600 dark:text-gray-300 uppercase">Statut</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {declarations.length === 0 && <tr><td colSpan={6} className="p-6 text-center text-gray-400">Aucune déclaration — générez une période ci-dessus</td></tr>}
                        {declarations.map((d) => {
                            const cfg = statusCfg[d.status] || statusCfg.pending;
                            return (
                                <tr key={d.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td className="p-3 font-mono font-semibold">{periodLabel(d)}</td>
                                    <td className="p-3">{d.tax_name}</td>
                                    <td className="p-3 text-right font-mono">{fmt(d.base_amount)}</td>
                                    <td className="p-3 text-right font-mono font-bold">{fmt(d.tax_amount)}</td>
                                    <td className="p-3 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold ' + cfg.cls}>{cfg.label}</span></td>
                                    <td className="p-3 text-right whitespace-nowrap">
                                        {d.status === 'pending' && (
                                            <button onClick={() => setStatus(d.id, 'filed')} className="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2">✅ Déposer</button>
                                        )}
                                        {d.status === 'filed' && (
                                            <button onClick={() => setStatus(d.id, 'paid')} className="text-green-600 hover:text-green-800 text-xs font-medium mr-2">💰 Payer</button>
                                        )}
                                        {(d.status === 'pending' || d.status === 'filed') && (
                                            <button onClick={() => setStatus(d.id, 'late')} className="text-red-600 hover:text-red-800 text-xs font-medium">⚠️ Retard</button>
                                        )}
                                        {d.status === 'paid' && <span className="text-xs text-gray-400">Soldée</span>}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

/* ═══════════ ONGLET ÉCHÉANCES ═══════════ */
function EcheancesTab({ deadlines }: { deadlines: DeadlineRow[] }) {
    const [modal, setModal] = useState(false);
    const [form, setForm] = useState({ name: '', deadline_date: new Date().toISOString().split('T')[0] });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        router.post(route('tax.deadlines.store'), form, { onSuccess: () => { setModal(false); setForm({ name: '', deadline_date: new Date().toISOString().split('T')[0] }); } });
    };

    const toggle = (d: DeadlineRow) => router.put(route('tax.deadlines.update', d.id), { status: d.status === 'done' ? 'pending' : 'done' });

    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md flex items-center gap-1">
                    <Plus className="h-4 w-4" /> Ajouter une échéance
                </button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Échéance</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Date limite</th>
                            <th className="p-3 text-center text-xs text-gray-600 dark:text-gray-300 uppercase">Statut</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {deadlines.length === 0 && <tr><td colSpan={4} className="p-6 text-center text-gray-400">Aucune échéance</td></tr>}
                        {deadlines.map((d) => {
                            const cfg = statusCfg[d.status] || statusCfg.pending;
                            return (
                                <tr key={d.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td className="p-3 font-medium">{d.name}</td>
                                    <td className="p-3">{d.deadline_date || '—'}</td>
                                    <td className="p-3 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold ' + cfg.cls}>{cfg.label}</span></td>
                                    <td className="p-3 text-right">
                                        <button onClick={() => toggle(d)} className="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                            {d.status === 'done' ? '↩️ Rouvrir' : '✅ Marquer faite'}
                                        </button>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {modal && (
                <Modal title="⏰ Nouvelle échéance" onClose={() => setModal(false)}>
                    <form onSubmit={submit} className="space-y-3">
                        <Field label="Nom *">
                            <input value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" placeholder="Déclaration TVA mensuelle" required />
                        </Field>
                        <Field label="Date limite *">
                            <input type="date" value={form.deadline_date} onChange={(e) => setForm((f) => ({ ...f, deadline_date: e.target.value }))} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                        </Field>
                        <div className="flex justify-end gap-2 pt-2">
                            <button type="button" onClick={() => setModal(false)} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                            <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Ajouter</button>
                        </div>
                    </form>
                </Modal>
            )}
        </div>
    );
}

/* ═══════════ COMPOSANTS COMMUNS ═══════════ */
function Modal({ title, onClose, children }: { title: string; onClose: () => void; children: ReactNode }) {
    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 className="font-bold text-gray-900 dark:text-gray-100">{title}</h3>
                    <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div className="p-5">{children}</div>
            </div>
        </div>
    );
}

function Field({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{label}</label>
            {children}
        </div>
    );
}
