import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import {
    ResponsiveContainer, BarChart, Bar, XAxis, YAxis, Tooltip, Legend,
    CartesianGrid, PieChart, Pie, Cell, LineChart, Line,
} from 'recharts';

interface TrialBalanceRow { number: string; name: string; class_number: number; type: string; total_debit: number | string; total_credit: number | string; }
interface PnlData { total_expenses: number; total_revenues: number; net_income: number; }
interface BalanceSheetData { total_assets: number; total_liabilities: number; net_income: number; }
interface MonthPerf { month: string; revenus: number; charges: number; }
interface ExpenseSlice { name: string; value: number; }
interface CashPoint { month: string; tresorerie: number; }
interface ChartsData { monthly: MonthPerf[]; expenseBreakdown: ExpenseSlice[]; cashflow: CashPoint[]; }

interface Props {
    trialBalance: TrialBalanceRow[];
    profitAndLoss: PnlData;
    balanceSheet: BalanceSheetData;
    charts: ChartsData;
    initialTab: string;
}

type TabKey = 'balance' | 'resultat' | 'bilan' | 'analyse';

const PIE_COLORS = ['#2563eb', '#16a34a', '#dc2626', '#9333ea', '#f59e0b', '#0ea5e9', '#ef4444', '#14b8a6'];

export default function Index({ trialBalance, profitAndLoss, balanceSheet, charts, initialTab }: Props) {
    const [activeTab, setActiveTab] = useState<TabKey>((initialTab as TabKey) || 'balance');

    useEffect(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', activeTab);
        window.history.replaceState({}, '', url.toString());
    }, [activeTab]);

    const tabs = [
        { key: 'balance' as TabKey, label: 'Balance Générale', icon: '⚖️' },
        { key: 'resultat' as TabKey, label: 'Compte de Résultat', icon: '📈' },
        { key: 'bilan' as TabKey, label: 'Bilan Comptable', icon: '🏦' },
        { key: 'analyse' as TabKey, label: 'Analyse & Graphiques', icon: '📊' },
    ];

    const exportUrl: Record<TabKey, string | null> = {
        balance: route('reporting.export-balance'),
        resultat: route('reporting.export-resultat'),
        bilan: route('reporting.export-bilan'),
        analyse: null,
    };

    const formatMoney = (value: number | string) =>
        parseFloat(String(value || 0)).toLocaleString('fr-FR') + ' FCFA';

    return (
        <ErpLayout>
            <Head title="États Financiers" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">États Financiers</h1>
                        <p className="text-sm text-gray-500 mt-1">Synthèse comptable en temps réel de l'entreprise active</p>
                    </div>

                    {/* ===== BARRE D'ONGLETS + EXPORT ===== */}
                    <div className="bg-white rounded-t-lg shadow-sm border-b border-gray-200 flex items-center">
                        <nav className="flex flex-1" aria-label="Tabs">
                            {tabs.map((tab) => {
                                const isActive = activeTab === tab.key;
                                return (
                                    <button
                                        key={tab.key}
                                        onClick={() => setActiveTab(tab.key)}
                                        className={
                                            'relative flex-1 py-4 px-4 text-center text-sm font-medium hover:bg-gray-50 transition ' +
                                            (isActive ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700')
                                        }
                                    >
                                        <span className="mr-2">{tab.icon}</span>
                                        <span>{tab.label}</span>
                                        <span
                                            aria-hidden="true"
                                            className={'absolute inset-x-0 bottom-0 h-0.5 ' + (isActive ? 'bg-indigo-600' : 'bg-transparent')}
                                        />
                                    </button>
                                );
                            })}
                        </nav>
                        {exportUrl[activeTab] && (
                            <a
                                href={exportUrl[activeTab] as string}
                                className="mr-4 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                ⬇ Exporter CSV
                            </a>
                        )}
                    </div>

                    <div className="bg-white rounded-b-lg shadow-sm p-6">
                        {activeTab === 'balance' && <BalanceTab data={trialBalance} formatMoney={formatMoney} />}
                        {activeTab === 'resultat' && <PnlTab data={profitAndLoss} formatMoney={formatMoney} />}
                        {activeTab === 'bilan' && <BalanceSheetTab data={balanceSheet} formatMoney={formatMoney} />}
                        {activeTab === 'analyse' && <AnalyseTab charts={charts} formatMoney={formatMoney} />}
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}

/* ===== ONGLET ANALYSE (Graphiques) ===== */
function AnalyseTab({ charts, formatMoney }: { charts: ChartsData; formatMoney: (v: number | string) => string }) {
    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="border border-gray-200 rounded-lg p-4">
                    <h3 className="font-bold text-gray-800 mb-4">📊 Revenus vs Charges (12 mois)</h3>
                    <div style={{ width: '100%', height: 300 }}>
                        <ResponsiveContainer>
                            <BarChart data={charts.monthly}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="month" />
                                <YAxis />
                                <Tooltip formatter={(value: any) => formatMoney(value)} />
                                <Legend />
                                <Bar dataKey="revenus" name="Revenus" fill="#16a34a" />
                                <Bar dataKey="charges" name="Charges" fill="#dc2626" />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                </div>
                <div className="border border-gray-200 rounded-lg p-4">
                    <h3 className="font-bold text-gray-800 mb-4">🥧 Répartition des Charges</h3>
                    <div style={{ width: '100%', height: 300 }}>
                        <ResponsiveContainer>
                            <PieChart>
                                <Pie data={charts.expenseBreakdown} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={100} label>
                                    {charts.expenseBreakdown.map((entry, index) => (
                                        <Cell key={index} fill={PIE_COLORS[index % PIE_COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip formatter={(value: any) => formatMoney(value)} />
                                <Legend />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </div>
            <div className="border border-gray-200 rounded-lg p-4">
                <h3 className="font-bold text-gray-800 mb-4">💧 Évolution de la Trésorerie</h3>
                <div style={{ width: '100%', height: 300 }}>
                    <ResponsiveContainer>
                        <LineChart data={charts.cashflow}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey="month" />
                            <YAxis />
                            <Tooltip formatter={(value: any) => formatMoney(value)} />
                            <Line type="monotone" dataKey="tresorerie" name="Trésorerie" stroke="#2563eb" strokeWidth={2} />
                        </LineChart>
                    </ResponsiveContainer>
                </div>
            </div>
        </div>
    );
}

/* ===== ONGLET BALANCE ===== */
function BalanceTab({ data, formatMoney }: { data: TrialBalanceRow[]; formatMoney: (v: number | string) => string }) {
    const totalDebit = data.reduce((s, r) => s + parseFloat(String(r.total_debit)), 0);
    const totalCredit = data.reduce((s, r) => s + parseFloat(String(r.total_credit)), 0);
    const isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;

    return (
        <div>
            <div className={'flex justify-between items-center mb-4 p-4 rounded-lg border ' + (isBalanced ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200')}>
                <p className={'text-lg font-bold ' + (isBalanced ? 'text-green-700' : 'text-red-700')}>
                    {isBalanced ? '✓ Balance Équilibrée' : '✗ Balance Déséquilibrée'}
                </p>
                <div className="grid grid-cols-3 gap-6 text-sm text-right">
                    <div><p className="text-xs text-gray-500">Total Débit</p><p className="font-bold font-mono">{formatMoney(totalDebit)}</p></div>
                    <div><p className="text-xs text-gray-500">Total Crédit</p><p className="font-bold font-mono">{formatMoney(totalCredit)}</p></div>
                    <div><p className="text-xs text-gray-500">Écart</p><p className="font-bold font-mono">{formatMoney(Math.abs(totalDebit - totalCredit))}</p></div>
                </div>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 border-b border-gray-200">
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">N° Compte</th>
                            <th className="p-3 text-left text-xs font-semibold text-gray-600 uppercase">Libellé</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Débit</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Crédit</th>
                            <th className="p-3 text-right text-xs font-semibold text-gray-600 uppercase">Solde</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {data.length === 0 ? (
                            <tr><td colSpan={5} className="p-8 text-center text-gray-500">Aucune écriture comptable enregistrée.</td></tr>
                        ) : data.map((row, i) => {
                            const debit = parseFloat(String(row.total_debit));
                            const credit = parseFloat(String(row.total_credit));
                            const solde = debit - credit;
                            return (
                                <tr key={i} className="hover:bg-gray-50">
                                    <td className="p-3 font-mono text-gray-700">{row.number}</td>
                                    <td className="p-3 text-gray-900">{row.name}</td>
                                    <td className="p-3 text-right font-mono">{debit.toLocaleString('fr-FR')}</td>
                                    <td className="p-3 text-right font-mono">{credit.toLocaleString('fr-FR')}</td>
                                    <td className={'p-3 text-right font-mono font-semibold ' + (solde >= 0 ? 'text-blue-600' : 'text-red-600')}>{solde.toLocaleString('fr-FR')}</td>
                                </tr>
                            );
                        })}
                    </tbody>
                    <tfoot className="bg-gray-50 font-bold border-t-2 border-gray-300">
                        <tr>
                            <td colSpan={2} className="p-3 text-right uppercase text-xs">Totaux</td>
                            <td className="p-3 text-right font-mono">{totalDebit.toLocaleString('fr-FR')}</td>
                            <td className="p-3 text-right font-mono">{totalCredit.toLocaleString('fr-FR')}</td>
                            <td className="p-3 text-right font-mono">{(totalDebit - totalCredit).toLocaleString('fr-FR')}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    );
}

/* ===== ONGLET RÉSULTAT ===== */
function PnlTab({ data, formatMoney }: { data: PnlData; formatMoney: (v: number | string) => string }) {
    const isBenefit = (data.net_income || 0) >= 0;
    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="p-6 rounded-lg bg-white border border-gray-200 border-t-4 border-t-red-500">
                    <h3 className="font-bold text-red-700 mb-2">Charges (Classe 6)</h3>
                    <p className="text-3xl font-bold text-red-900">{formatMoney(data.total_expenses)}</p>
                </div>
                <div className="p-6 rounded-lg bg-white border border-gray-200 border-t-4 border-t-green-500">
                    <h3 className="font-bold text-green-700 mb-2">Produits (Classe 7)</h3>
                    <p className="text-3xl font-bold text-green-900">{formatMoney(data.total_revenues)}</p>
                </div>
            </div>
            <div className={'p-8 rounded-lg text-center border-2 ' + (isBenefit ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200')}>
                <p className="text-xs text-gray-500 uppercase tracking-wider mb-2 font-semibold">Résultat net de l'exercice</p>
                <p className={'text-4xl font-bold ' + (isBenefit ? 'text-green-700' : 'text-red-700')}>{formatMoney(data.net_income)}</p>
                <p className={'text-sm mt-3 font-medium ' + (isBenefit ? 'text-green-600' : 'text-red-600')}>{isBenefit ? '✓ Bénéfice' : '✗ Perte'}</p>
            </div>
        </div>
    );
}

/* ===== ONGLET BILAN ===== */
function BalanceSheetTab({ data, formatMoney }: { data: BalanceSheetData; formatMoney: (v: number | string) => string }) {
    const isBalanced = Math.abs((data.total_assets || 0) - (data.total_liabilities || 0)) < 0.01;
    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="p-6 rounded-lg bg-white border border-gray-200 border-t-4 border-t-blue-500">
                    <h3 className="font-bold text-blue-800 mb-2">🏛️ ACTIF (Emplois)</h3>
                    <p className="text-3xl font-bold text-blue-900">{formatMoney(data.total_assets)}</p>
                </div>
                <div className="p-6 rounded-lg bg-white border border-gray-200 border-t-4 border-t-purple-500">
                    <h3 className="font-bold text-purple-800 mb-2">💼 PASSIF (Ressources)</h3>
                    <p className="text-3xl font-bold text-purple-900">{formatMoney(data.total_liabilities)}</p>
                </div>
            </div>
            <div className={'p-4 rounded-lg text-center border ' + (isBalanced ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200')}>
                <p className={'font-bold ' + (isBalanced ? 'text-green-700' : 'text-yellow-700')}>
                    {isBalanced ? '✓ Bilan équilibré (Actif = Passif)' : '⚠ Écart détecté entre Actif et Passif'}
                </p>
            </div>
        </div>
    );
}
