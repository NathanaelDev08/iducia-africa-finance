import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, ReactNode, useEffect, useState } from 'react';
import { Pencil, Plus, Trash2 } from 'lucide-react';

interface Tax {
    id: number;
    code: string;
    name: string;
    type: string;
    rate: number;
    account_number: string | null;
    is_active: boolean;
    effective_from: string | null;
    description: string | null;
}

interface SequenceRow {
    id: number;
    code: string;
    name: string;
    prefix: string;
    next_number: number;
    format: string;
}

interface Rate {
    id: number;
    company_id: number | null;
    currency_code: string;
    currency_name: string | null;
    rate_to_base: number;
    effective_from: string;
    is_active: boolean;
}

interface UserRow {
    id: number;
    name: string;
    email: string;
    companies: number[];
}

interface CompanyRow { id: number; name: string; }


interface CompanyInfo {
    id: number;
    name: string;
    slug: string;
    tax_number: string;
    currency: string;
    fiscal_year_start_month: number;
}

interface Props {
    company: CompanyInfo;
    general: Record<string, string>;
    taxes: Tax[];
    sequences: SequenceRow[];
    rates: Rate[];
    users: UserRow[];
    companies: CompanyRow[];
    initialTab: string;
}

type TabKey = 'general' | 'taxes' | 'sequences' | 'currencies' | 'imports' | 'users';

export default function Index({ company, general, taxes, sequences, rates, users, companies, initialTab }: Props) {
    const [activeTab, setActiveTab] = useState<TabKey>((initialTab as TabKey) || 'general');
    const flash: any = (usePage().props as any).flash;

    useEffect(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', activeTab);
        window.history.replaceState({}, '', url.toString());
    }, [activeTab]);

    const tabs: { key: TabKey; label: string; icon: string }[] = [
        { key: 'general', label: 'Entreprise & Général', icon: '🏢' },
        { key: 'taxes', label: 'Taxes & impôts', icon: '🧾' },
        { key: 'sequences', label: 'Numérotation', icon: '🔢' },
        { key: 'currencies', label: 'Devises', icon: '💱' },
        { key: 'imports', label: 'Imports', icon: '⬆️' },
        { key: 'users', label: 'Utilisateurs', icon: '👥' },
    ];

    return (
        <ErpLayout>
            <Head title="Paramétrage" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">⚙️ Paramétrage du système</h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">Entreprise, taxes, numérotation, devises, imports et utilisateurs</p>
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
                        {activeTab === 'general' && <GeneralTab company={company} general={general} />}
                        {activeTab === 'taxes' && <TaxesTab taxes={taxes} />}
                        {activeTab === 'sequences' && <SequencesTab sequences={sequences} />}
                        {activeTab === 'currencies' && <CurrenciesTab rates={rates} />}
                        {activeTab === 'imports' && <ImportsTab />}
                        {activeTab === 'users' && <UsersTab users={users} companies={companies} />}
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}

/* ═══════════ ONGLET GÉNÉRAL ═══════════ */
function GeneralTab({ company, general }: { company: CompanyInfo; general: Record<string, string> }) {
    const [form, setForm] = useState({
        name: company.name || '',
        tax_number: company.tax_number || '',
        currency: company.currency || 'XOF',
        fiscal_year_start_month: company.fiscal_year_start_month || 1,
        settings: {
            language: general['language'] || 'fr',
            timezone: general['timezone'] || 'Africa/Abidjan',
            invoice_payment_days: general['invoice_payment_days'] || '30',
        },
    });
    const [saving, setSaving] = useState(false);

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));
    const setSetting = (k: string, v: string) => setForm((f) => ({ ...f, settings: { ...f.settings, [k]: v } }));

    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    const submit = (e: FormEvent) => {
        e.preventDefault();
        setSaving(true);
        router.put(route('settings.general.update'), form, { onFinish: () => setSaving(false) });
    };

    return (
        <form onSubmit={submit} className="space-y-5 max-w-2xl">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Field label="Nom de l'entreprise *">
                    <input value={form.name} onChange={(e) => set('name', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <Field label="N° fiscal (RCCM / NCC)">
                    <input value={form.tax_number} onChange={(e) => set('tax_number', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                </Field>
                <Field label="Devise">
                    <select value={form.currency} onChange={(e) => set('currency', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="XOF">XOF — Franc CFA</option>
                        <option value="EUR">EUR — Euro</option>
                        <option value="USD">USD — Dollar</option>
                    </select>
                </Field>
                <Field label="Début de l'exercice fiscal">
                    <select value={form.fiscal_year_start_month} onChange={(e) => set('fiscal_year_start_month', parseInt(e.target.value))} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        {months.map((m, i) => <option key={i} value={i + 1}>{m}</option>)}
                    </select>
                </Field>
                <Field label="Langue">
                    <select value={form.settings.language} onChange={(e) => setSetting('language', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="fr">Français</option>
                        <option value="en">English</option>
                    </select>
                </Field>
                <Field label="Fuseau horaire">
                    <select value={form.settings.timezone} onChange={(e) => setSetting('timezone', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="Africa/Abidjan">Africa/Abidjan (GMT)</option>
                        <option value="Africa/Douala">Africa/Douala (GMT+1)</option>
                        <option value="Europe/Paris">Europe/Paris</option>
                    </select>
                </Field>
                <Field label="Délai de paiement factures (jours)">
                    <input type="number" min="0" value={form.settings.invoice_payment_days} onChange={(e) => setSetting('invoice_payment_days', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                </Field>
            </div>
            <button type="submit" disabled={saving} className="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium py-2.5 px-5 rounded-lg">
                {saving ? 'Enregistrement…' : '💾 Enregistrer les paramètres'}
            </button>
        </form>
    );
}

/* ═══════════ ONGLET TAXES ═══════════ */
function TaxesTab({ taxes }: { taxes: Tax[] }) {
    const [modal, setModal] = useState<null | { mode: 'create' } | { mode: 'edit'; tax: Tax }>(null);

    const typeLabels: Record<string, string> = {
        vat: 'TVA',
        withholding: 'Retenue à la source',
        income_tax: 'Impôt sur le revenu',
        other: 'Autre',
    };

    const remove = (tax: Tax) => {
        if (window.confirm(`Supprimer la taxe ${tax.code} ?`)) {
            router.delete(route('settings.taxes.destroy', tax.id));
        }
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
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Taux</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Compte</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Date d'effet</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {taxes.length === 0 && (
                            <tr><td colSpan={7} className="p-6 text-center text-gray-400">Aucune taxe configurée</td></tr>
                        )}
                        {taxes.map((tax) => (
                            <tr key={tax.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td className="p-3 font-mono font-semibold">{tax.code}</td>
                                <td className="p-3">{tax.name}</td>
                                <td className="p-3">
                                    <span className="px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                        {typeLabels[tax.type] || tax.type}
                                    </span>
                                </td>
                                <td className="p-3 text-right font-mono">{Number(tax.rate).toLocaleString('fr-FR')} %</td>
                                <td className="p-3 font-mono">{tax.account_number || '—'}</td>
                                <td className="p-3">{tax.effective_from || '—'}</td>
                                <td className="p-3 text-right">
                                    <button onClick={() => setModal({ mode: 'edit', tax })} className="text-indigo-600 hover:text-indigo-800 mr-3" title="Modifier"><Pencil className="h-4 w-4 inline" /></button>
                                    <button onClick={() => remove(tax)} className="text-red-600 hover:text-red-800" title="Supprimer"><Trash2 className="h-4 w-4 inline" /></button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {modal && <TaxModal mode={modal.mode} tax={modal.mode === 'edit' ? modal.tax : undefined} onClose={() => setModal(null)} />}
        </div>
    );
}

function TaxModal({ mode, tax, onClose }: { mode: 'create' | 'edit'; tax?: Tax; onClose: () => void }) {
    const [form, setForm] = useState({
        code: tax?.code || '',
        name: tax?.name || '',
        type: tax?.type || 'vat',
        rate: tax?.rate ?? 0,
        account_number: tax?.account_number || '',
        effective_from: tax?.effective_from || '',
        description: tax?.description || '',
    });

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (mode === 'create') router.post(route('settings.taxes.store'), form, { onSuccess: onClose });
        else router.put(route('settings.taxes.update', tax!.id), form, { onSuccess: onClose });
    };

    return (
        <Modal title={mode === 'create' ? '➕ Ajouter une taxe' : '✏️ Modifier la taxe'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                    <Field label="Code *">
                        <input value={form.code} onChange={(e) => set('code', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                    </Field>
                    <Field label="Type *">
                        <select value={form.type} onChange={(e) => set('type', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            <option value="vat">TVA</option>
                            <option value="withholding">Retenue à la source</option>
                            <option value="income_tax">Impôt sur le revenu</option>
                            <option value="other">Autre</option>
                        </select>
                    </Field>
                </div>
                <Field label="Nom *">
                    <input value={form.name} onChange={(e) => set('name', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <div className="grid grid-cols-3 gap-3">
                    <Field label="Taux (%) *">
                        <input type="number" step="0.01" min="0" max="100" value={form.rate} onChange={(e) => set('rate', parseFloat(e.target.value) || 0)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                    </Field>
                    <Field label="Compte comptable">
                        <input value={form.account_number} onChange={(e) => set('account_number', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" placeholder="4431" />
                    </Field>
                    <Field label="Date d'effet">
                        <input type="date" value={form.effective_from} onChange={(e) => set('effective_from', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                    </Field>
                </div>
                <Field label="Description">
                    <textarea value={form.description} onChange={(e) => set('description', e.target.value)} rows={2} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                </Field>
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Enregistrer</button>
                </div>
            </form>
        </Modal>
    );
}

/* ═══════════ ONGLET SÉQUENCES ═══════════ */
function SequencesTab({ sequences }: { sequences: SequenceRow[] }) {
    const [editing, setEditing] = useState<SequenceRow | null>(null);

    const preview = (s: { prefix: string; format: string; next_number: number }) => {
        const year = new Date().getFullYear();
        let out = s.format.replace('{prefix}', s.prefix).replace('{year}', String(year));
        out = out.replace(/\{number(?::(\d+))?\}/g, (_m: string, pad?: string) =>
            String(s.next_number).padStart(pad ? parseInt(pad) : 4, '0')
        );
        return out;
    };

    return (
        <div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Document</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Préfixe</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Prochain n°</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Format</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Aperçu</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {sequences.map((s) => (
                            <tr key={s.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td className="p-3 font-medium">{s.name}</td>
                                <td className="p-3 font-mono">{s.prefix || '—'}</td>
                                <td className="p-3 text-right font-mono">{s.next_number}</td>
                                <td className="p-3 font-mono text-xs">{s.format}</td>
                                <td className="p-3"><span className="px-2 py-1 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-mono text-xs">{preview(s)}</span></td>
                                <td className="p-3 text-right">
                                    <button onClick={() => setEditing(s)} className="text-indigo-600 hover:text-indigo-800" title="Modifier"><Pencil className="h-4 w-4 inline" /></button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {editing && <SequenceModal sequence={editing} onClose={() => setEditing(null)} />}
        </div>
    );
}

function SequenceModal({ sequence, onClose }: { sequence: SequenceRow; onClose: () => void }) {
    const [form, setForm] = useState({
        prefix: sequence.prefix || '',
        next_number: sequence.next_number,
        format: sequence.format,
    });

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    const submit = (e: FormEvent) => {
        e.preventDefault();
        router.put(route('settings.sequences.update', sequence.id), form, { onSuccess: onClose });
    };

    return (
        <Modal title={'🔢 Séquence : ' + sequence.name} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                <Field label="Préfixe">
                    <input value={form.prefix} onChange={(e) => set('prefix', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" />
                </Field>
                <Field label="Prochain numéro *">
                    <input type="number" min="1" value={form.next_number} onChange={(e) => set('next_number', parseInt(e.target.value) || 1)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <Field label="Format *">
                    <select value={form.format} onChange={(e) => set('format', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="{prefix}-{year}-{number:04}">{form.prefix}-2025-0001</option>
                        <option value="{prefix}-{number:04}">{form.prefix}-0001</option>
                        <option value="{prefix}-{year}-{number:05}">{form.prefix}-2025-00001</option>
                        <option value="{prefix}-{year}-{number:06}">{form.prefix}-2025-000001</option>
                        <option value="{prefix}-{year}{number:03}">{form.prefix}-2025001</option>
                    </select>
                </Field>
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Enregistrer</button>
                </div>
            </form>
        </Modal>
    );
}

/* ═══════════ ONGLET DEVISES ═══════════ */
function CurrenciesTab({ rates }: { rates: Rate[] }) {
    const [modal, setModal] = useState<null | { mode: 'create' } | { mode: 'edit'; rate: Rate }>(null);

    const remove = (rate: Rate) => {
        if (window.confirm(`Supprimer le taux ${rate.currency_code} ?`)) {
            router.delete(route('settings.rates.destroy', rate.id));
        }
    };

    return (
        <div>
            <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
                <p className="text-sm text-gray-500 dark:text-gray-400">Taux de change vers la devise de base (XOF) — 1 unité de la devise = taux en XOF.</p>
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md flex items-center gap-1 shrink-0">
                    <Plus className="h-4 w-4" /> Ajouter un taux
                </button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Code</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Devise</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">1 unité → XOF</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Date d'effet</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {rates.length === 0 && (
                            <tr><td colSpan={5} className="p-6 text-center text-gray-400">Aucun taux de change configuré</td></tr>
                        )}
                        {rates.map((r) => (
                            <tr key={r.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td className="p-3 font-mono font-semibold">{r.currency_code}</td>
                                <td className="p-3">{r.currency_name || '—'}</td>
                                <td className="p-3 text-right font-mono">{Number(r.rate_to_base).toLocaleString('fr-FR')}</td>
                                <td className="p-3">{r.effective_from}</td>
                                <td className="p-3 text-right">
                                    <button onClick={() => setModal({ mode: 'edit', rate: r })} className="text-indigo-600 hover:text-indigo-800 mr-3" title="Modifier"><Pencil className="h-4 w-4 inline" /></button>
                                    <button onClick={() => remove(r)} className="text-red-600 hover:text-red-800" title="Supprimer"><Trash2 className="h-4 w-4 inline" /></button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {modal && <RateModal mode={modal.mode} rate={modal.mode === 'edit' ? modal.rate : undefined} onClose={() => setModal(null)} />}
        </div>
    );
}

function RateModal({ mode, rate, onClose }: { mode: 'create' | 'edit'; rate?: Rate; onClose: () => void }) {
    const [form, setForm] = useState({
        currency_code: rate?.currency_code || '',
        currency_name: rate?.currency_name || '',
        rate_to_base: rate?.rate_to_base ?? 1,
        effective_from: rate?.effective_from || new Date().toISOString().split('T')[0],
    });

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (mode === 'create') router.post(route('settings.rates.store'), form, { onSuccess: onClose });
        else router.put(route('settings.rates.update', rate!.id), form, { onSuccess: onClose });
    };

    return (
        <Modal title={mode === 'create' ? '➕ Ajouter un taux de change' : '✏️ Modifier le taux'} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                    <Field label="Code devise *">
                        <select value={form.currency_code} onChange={(e) => set('currency_code', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                            <option value="">— Choisir —</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="USD">USD — Dollar US</option>
                            <option value="GBP">GBP — Livre sterling</option>
                            <option value="CAD">CAD — Dollar canadien</option>
                            <option value="JPY">JPY — Yen japonais</option>
                            <option value="CNY">CNY — Yuan chinois</option>
                        </select>
                    </Field>
                    <Field label="Nom de la devise">
                        <input value={form.currency_name} onChange={(e) => set('currency_name', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" placeholder="Euro" />
                    </Field>
                </div>
                <div className="grid grid-cols-2 gap-3">
                    <Field label="Taux vers XOF *">
                        <input type="number" step="0.000001" min="0" value={form.rate_to_base} onChange={(e) => set('rate_to_base', parseFloat(e.target.value) || 0)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                    </Field>
                    <Field label="Date d'effet *">
                        <input type="date" value={form.effective_from} onChange={(e) => set('effective_from', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                    </Field>
                </div>
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Enregistrer</button>
                </div>
            </form>
        </Modal>
    );
}

/* ═══════════ ONGLET IMPORTS ═══════════ */
function ImportsTab() {
    return (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <ImportCard
                title="👥 Employés"
                desc="Importez plusieurs employés d'un coup via un fichier CSV."
                routeName="settings.import.employees"
                format="first_name,last_name,email,phone,hire_date"
            />
            <ImportCard
                title="📒 Écritures comptables"
                desc="Importez des écritures (lignes regroupées par référence)."
                routeName="settings.import.journal"
                format="entry_date,journal_code,reference,description,account_number,debit,credit"
            />
        </div>
    );
}

function ImportCard({ title, desc, routeName, format }: { title: string; desc: string; routeName: string; format: string }) {
    const [file, setFile] = useState<File | null>(null);
    const [uploading, setUploading] = useState(false);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (!file) return;
        setUploading(true);
        router.post(route(routeName), { file }, {
            forceFormData: true,
            onSuccess: () => setFile(null),
            onFinish: () => setUploading(false),
        });
    };

    return (
        <form onSubmit={submit} className="border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <h3 className="font-semibold text-gray-900 dark:text-gray-100">{title}</h3>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{desc}</p>
            <div className="mt-4">
                <input
                    type="file"
                    accept=".csv,.txt"
                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                    className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
            </div>
            <p className="text-xs text-gray-400 mt-2 font-mono">Colonnes attendues : {format}</p>
            <button
                type="submit"
                disabled={!file || uploading}
                className="mt-4 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium py-2 px-4 rounded-md"
            >
                {uploading ? '⏳ Import en cours…' : '📤 Importer'}
            </button>
        </form>
    );
}

/* ═══════════ ONGLET UTILISATEURS ═══════════ */
function UsersTab({ users, companies }: { users: UserRow[]; companies: CompanyRow[] }) {
    const [modal, setModal] = useState<null | { mode: 'create' } | { mode: 'edit'; user: UserRow }>(null);

    const companyName = (id: number) => companies.find((c) => c.id === id)?.name || ('#' + id);

    return (
        <div>
            <div className="flex justify-end mb-4">
                <button onClick={() => setModal({ mode: 'create' })} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md flex items-center gap-1">
                    <Plus className="h-4 w-4" /> Ajouter un utilisateur
                </button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Nom</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Email</th>
                            <th className="p-3 text-left text-xs text-gray-600 dark:text-gray-300 uppercase">Entreprises autorisées</th>
                            <th className="p-3 text-right text-xs text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                        {users.map((u) => (
                            <tr key={u.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td className="p-3 font-medium">{u.name}</td>
                                <td className="p-3 text-gray-500 dark:text-gray-400">{u.email}</td>
                                <td className="p-3">
                                    <div className="flex flex-wrap gap-1">
                                        {u.companies.length === 0 && <span className="text-gray-400 text-xs">Aucune</span>}
                                        {u.companies.map((cid) => (
                                            <span key={cid} className="px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                                {companyName(cid)}
                                            </span>
                                        ))}
                                    </div>
                                </td>
                                <td className="p-3 text-right">
                                    <button onClick={() => setModal({ mode: 'edit', user: u })} className="text-indigo-600 hover:text-indigo-800" title="Modifier"><Pencil className="h-4 w-4 inline" /></button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {modal && <UserModal mode={modal.mode} user={modal.mode === 'edit' ? modal.user : undefined} companies={companies} onClose={() => setModal(null)} />}
        </div>
    );
}

function UserModal({ mode, user, companies, onClose }: { mode: 'create' | 'edit'; user?: UserRow; companies: CompanyRow[]; onClose: () => void }) {
    const [form, setForm] = useState({
        name: user?.name || '',
        email: user?.email || '',
        password: '',
        companies: user?.companies || [],
    });

    const set = (k: string, v: any) => setForm((f) => ({ ...f, [k]: v }));

    const toggleCompany = (id: number) => {
        setForm((f) => ({
            ...f,
            companies: f.companies.includes(id) ? f.companies.filter((c) => c !== id) : [...f.companies, id],
        }));
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (mode === 'create') router.post(route('settings.users.store'), form, { onSuccess: onClose });
        else router.put(route('settings.users.update', user!.id), form, { onSuccess: onClose });
    };

    return (
        <Modal title={mode === 'create' ? '➕ Ajouter un utilisateur' : "✏️ Modifier l'utilisateur"} onClose={onClose}>
            <form onSubmit={submit} className="space-y-3">
                <Field label="Nom complet *">
                    <input value={form.name} onChange={(e) => set('name', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <Field label="Email *">
                    <input type="email" value={form.email} onChange={(e) => set('email', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required />
                </Field>
                <Field label={mode === 'create' ? 'Mot de passe *' : 'Nouveau mot de passe (laisser vide pour ne pas changer)'}>
                    <input type="password" value={form.password} onChange={(e) => set('password', e.target.value)} className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required={mode === 'create'} minLength={6} />
                </Field>
                <Field label="Entreprises autorisées">
                    <div className="space-y-1 border border-gray-200 dark:border-gray-600 rounded-md p-3 max-h-40 overflow-y-auto">
                        {companies.map((c) => (
                            <label key={c.id} className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200 cursor-pointer">
                                <input type="checkbox" checked={form.companies.includes(c.id)} onChange={() => toggleCompany(c.id)} className="rounded border-gray-300" />
                                {c.name}
                            </label>
                        ))}
                    </div>
                </Field>
                <div className="flex justify-end gap-2 pt-2">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600">Annuler</button>
                    <button type="submit" className="px-4 py-2 text-sm rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">Enregistrer</button>
                </div>
            </form>
        </Modal>
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
