import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface PayRun { id: number; name: string; reference: string; period_start: string; period_end: string; status: string; payslips_count: number; total_net: number; total_gross: number; total_employer: number; accounting_entry: { id: number; reference: string } | null; }
interface Payslip { id: number; employee: { full_name: string; matricule: string }; pay_run: { name: string }; gross_salary: number; total_deductions: number; net_salary: number; }
interface PayItemRow { code: string; name: string; type: string; rate: number | null; fixed_amount: number | null; ceiling: number | null; }
interface ContributionRow { code: string; name: string; organism: string; employee_rate: number; employer_rate: number; ceiling: number | null; }

interface Props {
    activeTab: 'periodes' | 'bulletins' | 'calculs' | 'integration';
    payRuns?: PayRun[];
    payslips?: Payslip[];
    payItems?: PayItemRow[];
    contributions?: ContributionRow[];
    stats?: { total_runs: number; total_employees_active: number };
}

const STATUS_CONFIG: Record<string, { label: string; color: string; icon: string }> = {
    draft: { label: 'Brouillon', color: 'bg-gray-100 text-gray-800', icon: '📝' },
    calculated: { label: 'Calculée', color: 'bg-blue-100 text-blue-800', icon: '🔢' },
    validated: { label: 'Validée', color: 'bg-green-100 text-green-800', icon: '✓' },
    posted: { label: 'Comptabilisée', color: 'bg-purple-100 text-purple-800', icon: '📊' },
    locked: { label: 'Verrouillée', color: 'bg-red-100 text-red-800', icon: '🔒' },
};

const formatMoney = (v: number) => (v || 0).toLocaleString('fr-FR') + ' FCFA';

export default function Index({ activeTab, payRuns = [], payslips = [], payItems = [], contributions = [], stats }: Props) {
    const [showCreateModal, setShowCreateModal] = useState(false);

    const tabs = [
        { key: 'periodes', label: 'Périodes de paie', icon: '📅', href: route('payroll.index') },
        { key: 'bulletins', label: 'Bulletins', icon: '📄', href: route('payroll.bulletins') },
        { key: 'calculs', label: 'Calculs & Paramètres', icon: '🔢', href: route('payroll.calculs') },
        { key: 'integration', label: 'Intégration comptable', icon: '📊', href: route('payroll.integration') },
    ];

    return (
        <ErpLayout>
            <Head title="Paie" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">💰 Gestion de la Paie</h1>
                            <p className="text-sm text-gray-500 mt-1">Périodes, bulletins, calculs et intégration comptable</p>
                        </div>
                        {activeTab === 'periodes' && (
                            <button onClick={() => setShowCreateModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-5 rounded-lg text-sm shadow-sm">
                                + Nouvelle période
                            </button>
                        )}
                    </div>

                    {/* Stats uniquement sur l'onglet Périodes */}
                    {activeTab === 'periodes' && stats && (
                        <div className="grid grid-cols-2 gap-4 mb-6">
                            <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-indigo-500">
                                <p className="text-xs text-gray-500 uppercase">Périodes créées</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">{stats.total_runs}</p>
                            </div>
                            <div className="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                                <p className="text-xs text-gray-500 uppercase">Employés actifs</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">{stats.total_employees_active}</p>
                            </div>
                        </div>
                    )}

                    {/* BARRE D'ONGLETS = LIENS SERVEUR */}
                    <div className="bg-white rounded-t-lg shadow-sm border-b border-gray-200">
                        <nav className="flex" aria-label="Tabs">
                            {tabs.map((tab) => {
                                const isActive = activeTab === tab.key;
                                return (
                                    <Link key={tab.key} href={tab.href}
                                        className={'relative flex-1 py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 transition ' + (isActive ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700')}>
                                        <span className="mr-2">{tab.icon}</span>
                                        <span>{tab.label}</span>
                                        <span aria-hidden="true" className={'absolute inset-x-0 bottom-0 h-0.5 ' + (isActive ? 'bg-indigo-600' : 'bg-transparent')} />
                                    </Link>
                                );
                            })}
                        </nav>
                    </div>

                    <div className="bg-white rounded-b-lg shadow-sm p-6">
                        {activeTab === 'periodes' && <PeriodsTab payRuns={payRuns} />}
                        {activeTab === 'bulletins' && <PayslipsTab payslips={payslips} />}
                        {activeTab === 'calculs' && <CalculsTab payItems={payItems} contributions={contributions} />}
                        {activeTab === 'integration' && <IntegrationTab payRuns={payRuns} />}
                    </div>
                </div>
            </div>

            {showCreateModal && <CreateModal onClose={() => setShowCreateModal(false)} />}
        </ErpLayout>
    );
}

function PeriodsTab({ payRuns }: { payRuns: PayRun[] }) {
    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b"><tr>
                    <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Période</th>
                    <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Dates</th>
                    <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">Bulletins</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Brut</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Net</th>
                    <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">Statut</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr></thead>
                <tbody className="divide-y divide-gray-100">
                    {payRuns.length === 0 ? <tr><td colSpan={7} className="p-8 text-center text-gray-500">Aucune période. Créez-en une avec « + Nouvelle période ».</td></tr> :
                    payRuns.map((pr) => {
                        const cfg = STATUS_CONFIG[pr.status] || STATUS_CONFIG.draft;
                        return (
                            <tr key={pr.id} className="hover:bg-gray-50">
                                <td className="p-3"><div className="font-medium text-gray-900">{pr.name}</div><div className="text-xs text-gray-500 font-mono">{pr.reference}</div></td>
                                <td className="p-3 text-xs text-gray-600">{new Date(pr.period_start).toLocaleDateString('fr-FR')} → {new Date(pr.period_end).toLocaleDateString('fr-FR')}</td>
                                <td className="p-3 text-center"><span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold text-xs">{pr.payslips_count}</span></td>
                                <td className="p-3 text-right font-mono text-xs">{formatMoney(pr.total_gross)}</td>
                                <td className="p-3 text-right font-mono text-xs font-semibold text-green-700">{formatMoney(pr.total_net)}</td>
                                <td className="p-3 text-center"><span className={'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold ' + cfg.color}>{cfg.icon} {cfg.label}</span></td>
                                <td className="p-3 text-right"><Link href={route('payroll.show', pr.id)} className="inline-flex bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-md">Ouvrir →</Link></td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

function PayslipsTab({ payslips }: { payslips: Payslip[] }) {
    const [search, setSearch] = useState('');
    const filtered = payslips.filter((ps) => !search || ps.employee.full_name.toLowerCase().includes(search.toLowerCase()) || ps.employee.matricule.toLowerCase().includes(search.toLowerCase()));
    return (
        <div>
            <div className="mb-4"><input type="text" placeholder="Rechercher (nom, matricule)..." value={search} onChange={(e) => setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs" /></div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b"><tr>
                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Matricule</th>
                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Employé</th>
                        <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Période</th>
                        <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Brut</th>
                        <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Net</th>
                        <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">PDF</th>
                    </tr></thead>
                    <tbody className="divide-y divide-gray-100">
                        {filtered.length === 0 ? <tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucun bulletin.</td></tr> :
                        filtered.map((ps) => (
                            <tr key={ps.id} className="hover:bg-gray-50">
                                <td className="p-3 font-mono text-xs">{ps.employee.matricule}</td>
                                <td className="p-3 font-medium text-gray-900">{ps.employee.full_name}</td>
                                <td className="p-3 text-xs text-gray-600">{ps.pay_run.name}</td>
                                <td className="p-3 text-right font-mono">{formatMoney(ps.gross_salary)}</td>
                                <td className="p-3 text-right font-mono font-bold text-green-700">{formatMoney(ps.net_salary)}</td>
                                <td className="p-3 text-center"><div className="flex items-center justify-center gap-1.5">
<a href={route('payroll.payslip.view', ps.id)} target="_blank" title="Aperçu du bulletin" className="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-2 py-1.5 rounded-md">👁 Voir</a>
<a href={route('payroll.payslip.pdf', ps.id)} title="Télécharger le PDF" className="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-2 py-1.5 rounded-md">📄 PDF</a>
<button type="button" onClick={() => { if (window.confirm('Supprimer ce bulletin ?')) router.delete(route('payroll.payslip.destroy', ps.id)); }} title="Supprimer" className="inline-flex items-center bg-white border border-red-300 text-red-600 hover:bg-red-50 text-xs font-medium px-2 py-1.5 rounded-md">🗑</button>
</div></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function CalculsTab({ payItems, contributions }: { payItems: PayItemRow[]; contributions: ContributionRow[] }) {
    return (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h3 className="font-bold text-gray-800 mb-3">🔢 Rubriques de paie</h3>
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b"><tr>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Type</th>
                        <th className="p-2 text-right text-xs text-gray-600 uppercase">Taux / Montant</th>
                    </tr></thead>
                    <tbody className="divide-y divide-gray-100">
                        {payItems.length === 0 ? <tr><td colSpan={4} className="p-4 text-center text-gray-500">Aucune rubrique.</td></tr> :
                        payItems.map((item, i) => (
                            <tr key={i}>
                                <td className="p-2 font-mono text-xs">{item.code}</td>
                                <td className="p-2">{item.name}</td>
                                <td className="p-2 text-xs text-gray-600">{item.type}</td>
                                <td className="p-2 text-right font-mono text-xs">{item.rate !== null ? item.rate + ' %' : item.fixed_amount !== null ? formatMoney(item.fixed_amount) : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div>
                <h3 className="font-bold text-gray-800 mb-3">🏥 Cotisations sociales (CNPS)</h3>
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b"><tr>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th>
                        <th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
                        <th className="p-2 text-right text-xs text-gray-600 uppercase">Salarié</th>
                        <th className="p-2 text-right text-xs text-gray-600 uppercase">Patronal</th>
                        <th className="p-2 text-right text-xs text-gray-600 uppercase">Plafond</th>
                    </tr></thead>
                    <tbody className="divide-y divide-gray-100">
                        {contributions.length === 0 ? <tr><td colSpan={5} className="p-4 text-center text-gray-500">Aucune cotisation.</td></tr> :
                        contributions.map((c, i) => (
                            <tr key={i}>
                                <td className="p-2 font-mono text-xs">{c.code}</td>
                                <td className="p-2">{c.name}</td>
                                <td className="p-2 text-right font-mono text-xs">{c.employee_rate} %</td>
                                <td className="p-2 text-right font-mono text-xs">{c.employer_rate} %</td>
                                <td className="p-2 text-right font-mono text-xs">{c.ceiling !== null ? formatMoney(c.ceiling) : '—'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function IntegrationTab({ payRuns }: { payRuns: PayRun[] }) {
    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b"><tr>
                    <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Période</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Net total</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Charges patronales</th>
                    <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">Statut</th>
                    <th className="p-3 text-center text-xs font-semibold text-gray-600 uppercase">Écriture comptable</th>
                    <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                </tr></thead>
                <tbody className="divide-y divide-gray-100">
                    {payRuns.length === 0 ? <tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucune période.</td></tr> :
                    payRuns.map((pr) => {
                        const cfg = STATUS_CONFIG[pr.status] || STATUS_CONFIG.draft;
                        return (
                            <tr key={pr.id} className="hover:bg-gray-50">
                                <td className="p-3"><div className="font-medium text-gray-900">{pr.name}</div><div className="text-xs text-gray-500 font-mono">{pr.reference}</div></td>
                                <td className="p-3 text-right font-mono text-xs font-semibold text-green-700">{formatMoney(pr.total_net)}</td>
                                <td className="p-3 text-right font-mono text-xs text-orange-700">{formatMoney(pr.total_employer)}</td>
                                <td className="p-3 text-center"><span className={'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold ' + cfg.color}>{cfg.icon} {cfg.label}</span></td>
                                <td className="p-3 text-center">
                                    {pr.accounting_entry ? (
                                        <span className="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">📊 OD #{pr.accounting_entry.id}</span>
                                    ) : (
                                        <span className="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Non comptabilisée</span>
                                    )}
                                </td>
                                <td className="p-3 text-right"><Link href={route('payroll.show', pr.id)} className="text-indigo-600 hover:underline text-xs font-medium">Gérer →</Link></td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

function CreateModal({ onClose }: { onClose: () => void }) {
    const defaultStart = new Date();
    const defaultStartDate = defaultStart.toISOString().slice(0, 10);
    const defaultEndDate = new Date(defaultStart.getFullYear(), defaultStart.getMonth() + 1, 0).toISOString().slice(0, 10);
    const [periodStart, setPeriodStart] = useState(defaultStartDate);
    const [periodEnd, setPeriodEnd] = useState(defaultEndDate);
    const [name, setName] = useState('');
    const [reference, setReference] = useState('');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handlePeriodChange = (value: string) => {
        setPeriodStart(value);
        const d = new Date(value);
        setPeriodEnd(new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10));
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true); setErrors({});
        router.post(route('payroll.store'), {
            name: name || ('Paie ' + periodStart.slice(0, 7)),
            reference: reference || ('PAIE-' + periodStart.slice(0, 7).replace('-', '')),
            period_start: periodStart, period_end: periodEnd, payment_date: null,
        }, {
            onError: (errs) => { setErrors(errs); setProcessing(false); },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white rounded-lg shadow-2xl max-w-lg w-full" onClick={(e) => e.stopPropagation()}>
                <form onSubmit={submit}>
                    <div className="p-6 border-b"><h2 className="text-xl font-bold text-gray-900">➕ Nouvelle période de paie</h2></div>
                    <div className="p-6 space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Début *</label><input type="date" value={periodStart} onChange={(e) => handlePeriodChange(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required /></div>
                            <div><label className="block text-sm font-medium text-gray-700 mb-1">Fin *</label><input type="date" value={periodEnd} onChange={(e) => setPeriodEnd(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required /></div>
                        </div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Nom</label><input type="text" value={name} onChange={(e) => setName(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Référence</label><input type="text" value={reference} onChange={(e) => setReference(e.target.value)} className="w-full rounded-md border-gray-300 text-sm font-mono" /></div>
                    </div>
                    <div className="p-6 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
                        <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Annuler</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50">{processing ? 'Création...' : 'Créer'}</button>
                    </div>
                </form>
            </div>
        </div>
    );
}
