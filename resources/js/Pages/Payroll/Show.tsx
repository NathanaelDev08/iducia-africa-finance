import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Payslip {
    id: number;
    employee: { id: number; full_name: string; matricule: string; department: string };
    base_salary: number;
    gross_salary: number;
    total_deductions: number;
    net_salary: number;
    employer_contributions: number;
}

interface AccountingEntryItem {
    account_number: string;
    account_name: string;
    description: string;
    debit: number;
    credit: number;
}

interface AccountingEntry {
    id: number;
    reference: string;
    entry_date: string;
    description: string;
    journal_name: string;
    total_debit: number;
    total_credit: number;
    items: AccountingEntryItem[];
}

interface HistoryEntry {
    description: string;
    user: string;
    created_at: string;
    properties: string | null;
}

interface PayRunDetails {
    id: number;
    name: string;
    reference: string;
    period_start: string;
    period_end: string;
    payment_date: string | null;
    status: string;
    is_locked: boolean;
    accounting_entry_id: number | null;
    payslips: Payslip[];
    totals: { gross: number; net: number; deductions: number; employer: number };
    accounting_entry: AccountingEntry | null;
    history: HistoryEntry[];
}

interface Props {
    payRun: PayRunDetails;
    initialTab: string;
}

type TabKey = 'overview' | 'bulletins' | 'comptabilite' | 'historique';

const STATUS_CONFIG: Record<string, { label: string; color: string; icon: string }> = {
    draft: { label: 'Brouillon', color: 'bg-gray-100 text-gray-800', icon: '📝' },
    calculated: { label: 'Calculée', color: 'bg-blue-100 text-blue-800', icon: '🔢' },
    validated: { label: 'Validée', color: 'bg-green-100 text-green-800', icon: '✓' },
    posted: { label: 'Comptabilisée', color: 'bg-purple-100 text-purple-800', icon: '📊' },
    locked: { label: 'Verrouillée', color: 'bg-red-100 text-red-800', icon: '🔒' },
};

const formatMoney = (v: number) => (v || 0).toLocaleString('fr-FR') + ' FCFA';

export default function Show({ payRun, initialTab }: Props) {
    const flash = (usePage().props as any).flash;
    const [activeTab, setActiveTab] = useState<TabKey>((initialTab as TabKey) || 'overview');
    const [confirmAction, setConfirmAction] = useState<{ label: string; route: string; color: string } | null>(null);

    useEffect(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', activeTab);
        window.history.replaceState({}, '', url.toString());
    }, [activeTab]);

    const executeAction = () => {
        if (!confirmAction) return;
        router.post(confirmAction.route, {}, { onFinish: () => setConfirmAction(null) });
    };

    const canCalculate = payRun.status === 'draft' && !payRun.is_locked;
    const canValidate = payRun.status === 'calculated' && !payRun.is_locked;
    const canPost = (payRun.status === 'calculated' || payRun.status === 'validated') && !payRun.accounting_entry_id && !payRun.is_locked;
    const canLock = payRun.status === 'posted' && !payRun.is_locked;

    const cfg = STATUS_CONFIG[payRun.status] || STATUS_CONFIG.draft;

    const tabs = [
        { key: 'overview' as TabKey, label: 'Vue d\'ensemble', icon: '📋' },
        { key: 'bulletins' as TabKey, label: 'Bulletins', icon: '📄' },
        { key: 'comptabilite' as TabKey, label: 'Comptabilité', icon: '📊' },
        { key: 'historique' as TabKey, label: 'Historique', icon: '🕐' },
    ];

    return (
        <ErpLayout>
            <Head title={payRun.name} />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                    {/* HEADER */}
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <Link href={route('payroll.index')} className="text-sm text-indigo-600 hover:underline mb-2 inline-block">← Retour aux périodes</Link>

                        <div className="flex flex-wrap justify-between items-start gap-4 mt-2">
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900">{payRun.name}</h1>
                                <div className="flex flex-wrap gap-4 mt-2 text-sm text-gray-600">
                                    <span>📋 Réf : <span className="font-mono">{payRun.reference}</span></span>
                                    <span>📅 {new Date(payRun.period_start).toLocaleDateString('fr-FR')} → {new Date(payRun.period_end).toLocaleDateString('fr-FR')}</span>
                                    {payRun.payment_date && <span>💳 Paiement : {new Date(payRun.payment_date).toLocaleDateString('fr-FR')}</span>}
                                    <span className={'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold ' + cfg.color}>
                                        {cfg.icon} {cfg.label}
                                    </span>
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {canCalculate && (
                                    <button onClick={() => setConfirmAction({ label: 'Calculer les bulletins', route: route('payroll.calculate', payRun.id), color: 'blue' })}
                                        className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                                        🔢 Calculer
                                    </button>
                                )}
                                {canValidate && (
                                    <button onClick={() => setConfirmAction({ label: 'Valider la période', route: route('payroll.validate', payRun.id), color: 'green' })}
                                        className="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                                        ✓ Valider
                                    </button>
                                )}
                                {canPost && (
                                    <button onClick={() => setConfirmAction({ label: 'Comptabiliser (OD de paie)', route: route('payroll.post', payRun.id), color: 'purple' })}
                                        className="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                                        📊 Comptabiliser
                                    </button>
                                )}
                                {canLock && (
                                    <button onClick={() => setConfirmAction({ label: 'Verrouiller définitivement', route: route('payroll.lock', payRun.id), color: 'red' })}
                                        className="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                                        🔒 Verrouiller
                                    </button>
                                )}
                            </div>
                        </div>

                        {flash?.success && <div className="mt-4 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
                        {flash?.error && <div className="mt-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}
                    </div>

                    {/* BARRE D'ONGLETS */}
                    <div className="bg-white rounded-t-lg shadow-sm border-b border-gray-200">
                        <nav className="flex" aria-label="Tabs">
                            {tabs.map((tab) => {
                                const isActive = activeTab === tab.key;
                                return (
                                    <button key={tab.key} onClick={() => setActiveTab(tab.key)}
                                        className={'relative flex-1 py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 transition ' + (isActive ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700')}>
                                        <span className="mr-2">{tab.icon}</span>
                                        <span>{tab.label}</span>
                                        <span aria-hidden="true" className={'absolute inset-x-0 bottom-0 h-0.5 ' + (isActive ? 'bg-indigo-600' : 'bg-transparent')} />
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="bg-white rounded-b-lg shadow-sm p-6">
                        {activeTab === 'overview' && <OverviewTab payRun={payRun} />}
                        {activeTab === 'bulletins' && <BulletinsTab payRun={payRun} />}
                        {activeTab === 'comptabilite' && <ComptabiliteTab payRun={payRun} />}
                        {activeTab === 'historique' && <HistoriqueTab payRun={payRun} />}
                    </div>
                </div>
            </div>

            {/* MODAL DE CONFIRMATION */}
            {confirmAction && (
                <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
                        <div className="p-6">
                            <h2 className="text-lg font-bold text-gray-900">Confirmer l'action</h2>
                            <p className="mt-2 text-sm text-gray-600">Êtes-vous sûr de vouloir <strong>{confirmAction.label}</strong> ?</p>
                        </div>
                        <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                            <button onClick={() => setConfirmAction(null)} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Annuler</button>
                            <button onClick={executeAction} className={`px-4 py-2 text-sm font-medium text-white rounded-md bg-${confirmAction.color}-600 hover:bg-${confirmAction.color}-700`}>Confirmer</button>
                        </div>
                    </div>
                </div>
            )}
        </ErpLayout>
    );
}

/* ===== ONGLET 1 : VUE D'ENSEMBLE ===== */
function OverviewTab({ payRun }: { payRun: PayRunDetails }) {
    return (
        <div className="space-y-6">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                    <p className="text-xs text-gray-500 uppercase">Salaire brut</p>
                    <p className="text-xl font-bold text-gray-900 mt-1">{formatMoney(payRun.totals.gross)}</p>
                </div>
                <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                    <p className="text-xs text-gray-500 uppercase">Retenues</p>
                    <p className="text-xl font-bold text-red-700 mt-1">{formatMoney(payRun.totals.deductions)}</p>
                </div>
                <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                    <p className="text-xs text-gray-500 uppercase">Net à payer</p>
                    <p className="text-xl font-bold text-green-700 mt-1">{formatMoney(payRun.totals.net)}</p>
                </div>
                <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-orange-500">
                    <p className="text-xs text-gray-500 uppercase">Charges patronales</p>
                    <p className="text-xl font-bold text-orange-700 mt-1">{formatMoney(payRun.totals.employer)}</p>
                </div>
            </div>

            <div className="bg-gray-50 rounded-lg p-6">
                <h3 className="font-bold text-gray-800 mb-4">📊 Synthèse de la période</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p className="text-sm text-gray-600 mb-2"><strong>Nombre de bulletins :</strong> {payRun.payslips.length}</p>
                        <p className="text-sm text-gray-600 mb-2"><strong>Période :</strong> du {new Date(payRun.period_start).toLocaleDateString('fr-FR')} au {new Date(payRun.period_end).toLocaleDateString('fr-FR')}</p>
                        {payRun.payment_date && <p className="text-sm text-gray-600 mb-2"><strong>Date de paiement :</strong> {new Date(payRun.payment_date).toLocaleDateString('fr-FR')}</p>}
                    </div>
                    <div>
                        <p className="text-sm text-gray-600 mb-2"><strong>Salaire moyen brut :</strong> {payRun.payslips.length > 0 ? formatMoney(payRun.totals.gross / payRun.payslips.length) : '—'}</p>
                        <p className="text-sm text-gray-600 mb-2"><strong>Salaire moyen net :</strong> {payRun.payslips.length > 0 ? formatMoney(payRun.totals.net / payRun.payslips.length) : '—'}</p>
                        <p className="text-sm text-gray-600 mb-2"><strong>Coût total entreprise :</strong> {formatMoney(payRun.totals.gross + payRun.totals.employer)}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

/* ===== ONGLET 2 : BULLETINS ===== */
function BulletinsTab({ payRun }: { payRun: PayRunDetails }) {
    const [search, setSearch] = useState('');
    const filtered = payRun.payslips.filter((ps) =>
        !search ||
        ps.employee.full_name.toLowerCase().includes(search.toLowerCase()) ||
        ps.employee.matricule.toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div>
            <div className="mb-4 flex justify-between items-center">
                <input type="text" placeholder="Rechercher (nom, matricule)..." value={search} onChange={(e) => setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs" />
                <span className="text-sm text-gray-500">{filtered.length} bulletin(s)</span>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Matricule</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Employé</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Département</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Brut</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Retenues</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Net</th>
                            <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">PDF</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {filtered.length === 0 ? (
                            <tr><td colSpan={7} className="p-8 text-center text-gray-500">
                                {payRun.payslips.length === 0 ? 'Aucun bulletin. Cliquez sur "Calculer" pour les générer.' : 'Aucun résultat pour cette recherche.'}
                            </td></tr>
                        ) : filtered.map((ps) => (
                            <tr key={ps.id} className="hover:bg-gray-50">
                                <td className="p-3 font-mono text-xs text-gray-700">{ps.employee.matricule}</td>
                                <td className="p-3 font-medium text-gray-900">{ps.employee.full_name}</td>
                                <td className="p-3 text-xs text-gray-600">{ps.employee.department}</td>
                                <td className="p-3 text-right font-mono">{formatMoney(ps.gross_salary)}</td>
                                <td className="p-3 text-right font-mono text-red-600">- {formatMoney(ps.total_deductions)}</td>
                                <td className="p-3 text-right font-mono font-bold text-green-700">{formatMoney(ps.net_salary)}</td>
                                <td className="p-3 text-center">
                                    <div className="flex items-center justify-center gap-1.5">
<a href={route('payroll.payslip.view', ps.id)} target="_blank" rel="noopener noreferrer" title="Aperçu du bulletin" className="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-2 py-1.5 rounded-md">👁 Voir</a>
<a href={route('payroll.payslip.pdf', ps.id)} title="Télécharger le PDF" className="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-2 py-1.5 rounded-md">📄 PDF</a>
<button type="button" onClick={() => { if (window.confirm('Supprimer ce bulletin ?')) router.delete(route('payroll.payslip.destroy', ps.id)); }} title="Supprimer" className="inline-flex items-center bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-medium px-2 py-1.5 rounded-md">🗑</button>
</div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

/* ===== ONGLET 3 : COMPTABILITÉ ===== */
function ComptabiliteTab({ payRun }: { payRun: PayRunDetails }) {
    if (!payRun.accounting_entry) {
        return (
            <div className="text-center py-12">
                <div className="text-6xl mb-4">📊</div>
                <h3 className="text-lg font-bold text-gray-700 mb-2">Aucune écriture comptable</h3>
                <p className="text-sm text-gray-500 mb-4">Cette période n'a pas encore été comptabilisée.</p>
                <p className="text-xs text-gray-400">Utilisez le bouton "Comptabiliser" dans l'en-tête pour générer l'OD de paie.</p>
            </div>
        );
    }

    const entry = payRun.accounting_entry;

    return (
        <div className="space-y-6">
            <div className="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div className="flex justify-between items-center">
                    <div>
                        <p className="text-xs text-purple-600 uppercase font-semibold">Écriture comptable générée</p>
                        <p className="text-lg font-bold text-purple-900 mt-1">OD #{entry.id} — {entry.reference}</p>
                    </div>
                    <div className="text-right text-sm text-purple-700">
                        <p>Journal : <strong>{entry.journal_name}</strong></p>
                        <p>Date : <strong>{new Date(entry.entry_date).toLocaleDateString('fr-FR')}</strong></p>
                    </div>
                </div>
            </div>

            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">N° Compte</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Libellé</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Débit</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Crédit</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {entry.items.map((item, i) => (
                            <tr key={i} className="hover:bg-gray-50">
                                <td className="p-3 font-mono text-xs">{item.account_number}</td>
                                <td className="p-3 text-gray-900">{item.account_name}</td>
                                <td className="p-3 text-xs text-gray-600">{item.description}</td>
                                <td className="p-3 text-right font-mono">{item.debit > 0 ? formatMoney(item.debit) : '—'}</td>
                                <td className="p-3 text-right font-mono">{item.credit > 0 ? formatMoney(item.credit) : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                    <tfoot className="bg-gray-50 font-bold border-t-2 border-gray-300">
                        <tr>
                            <td colSpan={3} className="p-3 text-right uppercase text-xs">Totaux</td>
                            <td className="p-3 text-right font-mono">{formatMoney(entry.total_debit)}</td>
                            <td className="p-3 text-right font-mono">{formatMoney(entry.total_credit)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {Math.abs(entry.total_debit - entry.total_credit) < 0.01 && (
                <div className="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <p className="text-green-800 font-semibold">✓ Écriture équilibrée (Débit = Crédit)</p>
                </div>
            )}
        </div>
    );
}

/* ===== ONGLET 4 : HISTORIQUE ===== */
function HistoriqueTab({ payRun }: { payRun: PayRunDetails }) {
    return (
        <div>
            <div className="mb-4">
                <p className="text-sm text-gray-600">Journal d'audit de cette période ({payRun.history.length} événement(s))</p>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Utilisateur</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {payRun.history.length === 0 ? (
                            <tr><td colSpan={3} className="p-8 text-center text-gray-500">Aucun événement enregistré.</td></tr>
                        ) : payRun.history.map((h, i) => (
                            <tr key={i} className="hover:bg-gray-50">
                                <td className="p-3 text-xs text-gray-600 font-mono">{h.created_at}</td>
                                <td className="p-3 font-medium text-gray-900">{h.user}</td>
                                <td className="p-3 text-gray-700">{h.description}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
