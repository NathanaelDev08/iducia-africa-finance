import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';

interface Props {
    company?: any;
    metrics?: any;
    recentInvoices?: any[];
    recentReceipts?: any[];
    recentPurchases?: any[];
    alerts?: any[];
}

const fmt = (v: any) => (Number(v) || 0).toLocaleString('fr-FR');
const fdate = (d: any) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('fr-FR'); } catch (e) { return String(d); }
};

const statusBadge = (s: string) => {
    const cfg: Record<string, string> = {
        paid: 'bg-green-100 text-green-800',
        pending: 'bg-amber-100 text-amber-800',
        overdue: 'bg-red-100 text-red-800',
    };
    const label: Record<string, string> = { paid: 'Payée', pending: 'En attente', overdue: 'En retard' };
    return <span className={'px-2 py-0.5 rounded-full text-xs font-semibold ' + (cfg[s] || cfg.pending)}>{label[s] || s}</span>;
};

export default function Dashboard({ 
    company = {}, 
    metrics = {}, 
    recentInvoices = [], 
    recentReceipts = [], 
    recentPurchases = [], 
    alerts = [] 
}: Props) {
    // Valeurs par défaut pour metrics
    const m = {
        revenue: metrics?.revenue || 0,
        expenses: metrics?.expenses || 0,
        cash: metrics?.cash || 0,
        clients: metrics?.clients || 0,
        suppliers: metrics?.suppliers || 0,
        invoices_pending: metrics?.invoices_pending || 0,
        invoices_pending_total: metrics?.invoices_pending_total || 0,
        receipts_count: metrics?.receipts_count || 0,
        receipts_total: metrics?.receipts_total || 0,
        employees: metrics?.employees || 0,
        payslips: metrics?.payslips || 0,
    };

    const cards = [
        { label: "Chiffre d'affaires HT", value: fmt(m.revenue) + ' F', border: 'border-green-500', color: 'text-green-600' },
        { label: 'Achats / Dépenses HT', value: fmt(m.expenses) + ' F', border: 'border-red-500', color: 'text-red-600' },
        { label: 'Trésorerie (classe 5)', value: fmt(m.cash) + ' F', border: 'border-blue-500', color: 'text-blue-600' },
        { label: 'Factures en attente', value: m.invoices_pending + ' (' + fmt(m.invoices_pending_total) + ' F)', border: 'border-amber-500', color: 'text-amber-600' },
        { label: 'Reçus encaissés', value: m.receipts_count + ' (' + fmt(m.receipts_total) + ' F)', border: 'border-indigo-500', color: 'text-indigo-600' },
        { label: 'Employés actifs', value: String(m.employees), border: 'border-purple-500', color: 'text-purple-600' },
    ];

    return (
        <ErpLayout>
            <Head title="Tableau de bord" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">📊 {company?.name || 'Tableau de bord'}</h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">Vue d'ensemble : facturation, encaissements, dépenses et trésorerie</p>
                    </div>

                    {/* ALERTES */}
                    {alerts.length > 0 && (
                        <div className="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                            <h2 className="text-sm font-bold text-red-800 mb-2">🚨 Alertes à traiter</h2>
                            <ul className="space-y-1">
                                {alerts.map((a: string, i: number) => (
                                    <li key={i} className="text-sm text-red-700">{a}</li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* CARTES KPI */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        {cards.map((c) => (
                            <div key={c.label} className={`bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-l-4 ${c.border}`}>
                                <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">{c.label}</p>
                                <p className={`text-xl font-bold mt-1 ${c.color}`}>{c.value}</p>
                            </div>
                        ))}
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* FACTURES CLIENTS */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                            <h2 className="font-bold text-gray-900 dark:text-gray-100 mb-3">🧾 Dernières factures clients</h2>
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                                        <tr>
                                            <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">N°</th>
                                            <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">Client</th>
                                            <th className="p-2 text-right text-xs uppercase text-gray-600 dark:text-gray-300">Montant TTC</th>
                                            <th className="p-2 text-center text-xs uppercase text-gray-600 dark:text-gray-300">Statut</th>
                                            <th className="p-2 text-center text-xs uppercase text-gray-600 dark:text-gray-300">Docs</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                                        {recentInvoices.length === 0 && <tr><td colSpan={5} className="p-4 text-center text-gray-400">Aucune facture</td></tr>}
                                        {recentInvoices.map((inv: any) => (
                                            <tr key={inv.id}>
                                                <td className="p-2 font-mono text-xs">{inv.reference}</td>
                                                <td className="p-2">{inv.client}</td>
                                                <td className="p-2 text-right font-mono">{fmt(inv.total)}</td>
                                                <td className="p-2 text-center">{statusBadge(inv.status)}</td>
                                                <td className="p-2 text-center whitespace-nowrap">
                                                    <a href={route('documents.invoice.view', inv.id)} target="_blank" className="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2" title="Aperçu">👁</a>
                                                    <a href={route('documents.invoice.pdf', inv.id)} className="text-red-600 hover:text-red-800 text-xs font-medium" title="PDF">📄</a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {/* REÇUS */}
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                            <h2 className="font-bold text-gray-900 dark:text-gray-100 mb-3">💰 Derniers reçus de paiement</h2>
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                                        <tr>
                                            <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">N°</th>
                                            <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">Client</th>
                                            <th className="p-2 text-right text-xs uppercase text-gray-600 dark:text-gray-300">Montant</th>
                                            <th className="p-2 text-center text-xs uppercase text-gray-600 dark:text-gray-300">Docs</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                                        {recentReceipts.length === 0 && <tr><td colSpan={4} className="p-4 text-center text-gray-400">Aucun reçu</td></tr>}
                                        {recentReceipts.map((r: any) => (
                                            <tr key={r.id}>
                                                <td className="p-2 font-mono text-xs">{r.reference}</td>
                                                <td className="p-2">{r.client}</td>
                                                <td className="p-2 text-right font-mono text-green-700">{fmt(r.amount)}</td>
                                                <td className="p-2 text-center whitespace-nowrap">
                                                    <a href={route('documents.receipt.view', r.id)} target="_blank" className="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2" title="Aperçu">👁</a>
                                                    <a href={route('documents.receipt.pdf', r.id)} className="text-red-600 hover:text-red-800 text-xs font-medium" title="PDF">📄</a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {/* FACTURES FOURNISSEURS */}
                    <div className="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                        <h2 className="font-bold text-gray-900 dark:text-gray-100 mb-3">📥 Dernières factures fournisseurs</h2>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 dark:bg-gray-700 border-b">
                                    <tr>
                                        <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">N°</th>
                                        <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">Fournisseur</th>
                                        <th className="p-2 text-left text-xs uppercase text-gray-600 dark:text-gray-300">Date</th>
                                        <th className="p-2 text-right text-xs uppercase text-gray-600 dark:text-gray-300">Montant TTC</th>
                                        <th className="p-2 text-center text-xs uppercase text-gray-600 dark:text-gray-300">Statut</th>
                                        <th className="p-2 text-center text-xs uppercase text-gray-600 dark:text-gray-300">Docs</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                                    {recentPurchases.length === 0 && <tr><td colSpan={6} className="p-4 text-center text-gray-400">Aucune facture fournisseur</td></tr>}
                                    {recentPurchases.map((p: any) => (
                                        <tr key={p.id}>
                                            <td className="p-2 font-mono text-xs">{p.reference}</td>
                                            <td className="p-2">{p.supplier}</td>
                                            <td className="p-2">{fdate(p.date)}</td>
                                            <td className="p-2 text-right font-mono">{fmt(p.total)}</td>
                                            <td className="p-2 text-center">{statusBadge(p.status)}</td>
                                            <td className="p-2 text-center whitespace-nowrap">
                                                <a href={route('documents.purchase.view', p.id)} target="_blank" className="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2" title="Aperçu">👁</a>
                                                <a href={route('documents.purchase.pdf', p.id)} className="text-red-600 hover:text-red-800 text-xs font-medium" title="PDF">📄</a>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}
