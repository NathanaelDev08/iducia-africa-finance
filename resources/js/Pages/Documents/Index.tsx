import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import DataTable, { Column } from '@/Components/DataTable';

interface Doc {
    id: number; type: string; label: string; reference: string;
    date: string | null; party: string; total: number; status: string;
    view: string; pdf: string;
}

interface Props { documents: Doc[]; company: any; }

const fmt = (v: number) => (Number(v) || 0).toLocaleString('fr-FR');
const fdate = (d: string | null) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('fr-FR'); } catch (e) { return String(d); }
};

const STATUS: Record<string, { label: string; cls: string }> = {
    paid: { label: 'Payée', cls: 'bg-green-100 text-green-800' },
    pending: { label: 'En attente', cls: 'bg-amber-100 text-amber-800' },
    draft: { label: 'Brouillon', cls: 'bg-gray-100 text-gray-800' },
    validated: { label: 'Validée', cls: 'bg-blue-100 text-blue-800' },
    sent: { label: 'Envoyé', cls: 'bg-blue-100 text-blue-800' },
    accepted: { label: 'Accepté', cls: 'bg-green-100 text-green-800' },
    received: { label: 'Reçu', cls: 'bg-green-100 text-green-800' },
    invoiced: { label: 'Facturé', cls: 'bg-purple-100 text-purple-800' },
    overdue: { label: 'En retard', cls: 'bg-red-100 text-red-800' },
    refused: { label: 'Refusé', cls: 'bg-red-100 text-red-800' },
    cancelled: { label: 'Annulée', cls: 'bg-red-100 text-red-800' },
    calculated: { label: 'Calculée', cls: 'bg-blue-100 text-blue-800' },
};

const TABS = [
    { key: 'all', label: '📚 Tous' },
    { key: 'facture_vente', label: '🧾 Factures vente' },
    { key: 'facture_achat', label: '📥 Factures achat' },
    { key: 'devis', label: '📑 Devis' },
    { key: 'commande', label: '🛒 Commandes' },
    { key: 'recu_client', label: '💰 Reçus clients' },
    { key: 'recu_fournisseur', label: '💸 Reçus fournisseurs' },
];

export default function Index({ documents, company }: Props) {
    const [tab, setTab] = useState('all');

    const filtered = tab === 'all' ? documents : documents.filter((d) => d.type === tab);

    const columns: Column<Doc>[] = [
        { key: 'label', label: 'Type' },
        { key: 'reference', label: 'N°' },
        { key: 'date', label: 'Date', render: (d) => fdate(d.date) },
        { key: 'party', label: 'Tiers' },
        {
            key: 'total', label: 'Montant TTC', align: 'right',
            render: (d) => <span className="font-mono font-bold">{fmt(d.total)}</span>,
        },
        {
            key: 'status', label: 'Statut', align: 'center',
            render: (d) => {
                const st = STATUS[d.status] || { label: d.status, cls: 'bg-gray-100 text-gray-800' };
                return <span className={'px-2 py-0.5 rounded-full text-xs font-semibold ' + st.cls}>{st.label}</span>;
            },
        },
        {
            key: 'id', label: 'Documents', align: 'center', sortable: false,
            render: (d) => (
                <span className="whitespace-nowrap">
                    <a href={route(d.view, d.id)} target="_blank" className="text-blue-600 hover:text-blue-800 text-sm mr-2" title="Aperçu dans le navigateur">👁</a>
                    <a href={route(d.pdf, d.id)} className="text-red-600 hover:text-red-800 text-sm" title="Télécharger le PDF">📄</a>
                </span>
            ),
        },
    ];

    return (
        <ErpLayout>
            <Head title="Documents" />
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">📄 Centre des documents</h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Factures, devis, commandes et reçus de {company?.name} — recherche, tri et pagination optimisés
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2 mb-5">
                        {TABS.map((t) => (
                            <button
                                key={t.key}
                                onClick={() => setTab(t.key)}
                                className={`px-3 py-1.5 rounded-full text-xs font-semibold transition ${
                                    tab === t.key
                                        ? 'bg-[#1a3a6a] text-white shadow'
                                        : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-gray-100'
                                }`}
                            >
                                {t.label}
                            </button>
                        ))}
                    </div>

                    <DataTable
                        columns={columns}
                        data={filtered}
                        title="Liste des documents"
                        searchPlaceholder="🔍 Rechercher (n°, tiers, type…)"
                        emptyMessage="Aucun document trouvé — créez des factures, devis ou reçus pour les voir ici"
                        initialPerPage={25}
                        defaultSort={{ key: 'date', dir: 'desc' }}
                        rowKey={(d) => d.type + '-' + d.id}
                    />
                </div>
            </div>
        </ErpLayout>
    );
}
