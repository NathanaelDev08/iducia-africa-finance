import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { FileText, BookOpen, Notebook, Scale, List, Upload } from 'lucide-react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Account { id: number; number: string; name: string; class_number: number; type: string; is_active: boolean; }

export default function PlanComptable({ accounts, activeTab }: PageProps<{ accounts: Account[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');
    const [importOpen, setImportOpen] = useState(false);

    const tabs = [
        { label: 'Écritures', href: route('accounting.index'), active: activeTab === 'ecritures', icon: FileText },
        { label: 'Plan Comptable', href: route('accounting.plan'), active: activeTab === 'plan', icon: List },
        { label: 'Journaux', href: route('accounting.journals'), active: activeTab === 'journaux', icon: Notebook },
        { label: 'Balance', href: route('accounting.balance'), active: activeTab === 'balance', icon: Scale },
        { label: 'Grand Livre', href: route('accounting.grand-livre'), active: activeTab === 'grand-livre', icon: BookOpen },
    ];

    return (
        <ErpLayout>
            <Head title="Plan Comptable" />
            <div className="py-2">
                <div className="mb-2 flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Plan Comptable SYSCOHADA</h1>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setImportOpen(true)}
                            className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            <Upload className="h-4 w-4" /> Importer (CSV)
                        </button>
                        <ViewSwitcher value={view} onChange={setView} />
                    </div>
                </div>
                <p className="mb-4 text-xs text-gray-500 dark:text-gray-400">
                    Colonnes CSV attendues : <strong>Numéro</strong>, <strong>Libellé</strong>, Classe (optionnel, déduite du numéro si absente), Type (optionnel, déduit de la classe SYSCOHADA si absent).
                </p>
                <Tabs tabs={tabs} />
                {view === 'list' ? (
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Compte</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Intitulé</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Classe</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {accounts.length === 0 ? (
                                <tr><td colSpan={5} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun compte.</td></tr>
                            ) : (
                                accounts.map((account) => (
                                    <tr key={account.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{account.number}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{account.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{account.class_number}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{account.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {account.is_active ? 'Actif' : 'Inactif'}
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                ) : (
                <KanbanBoard
                    data={accounts}
                    rowKey={(a) => a.id}
                    groupBy={(a) => (a.is_active ? 'active' : 'inactive')}
                    columns={[
                        { key: 'active', label: 'Actifs', colorClass: 'bg-green-100 text-green-800' },
                        { key: 'inactive', label: 'Inactifs', colorClass: 'bg-red-100 text-red-800' },
                    ]}
                    emptyMessage="Aucun compte."
                    renderCard={(account) => (
                        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div className="font-mono text-xs text-gray-500 dark:text-gray-400">{account.number}</div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">{account.name}</div>
                            <div className="mt-1 flex justify-between text-xs text-gray-500 dark:text-gray-300">
                                <span>Classe {account.class_number}</span>
                                <span>{account.type}</span>
                            </div>
                        </div>
                    )}
                />
                )}
            </div>
            {importOpen && <ImportAccountsModal onClose={() => setImportOpen(false)} />}
        </ErpLayout>
    );
}

function ImportAccountsModal({ onClose }: Readonly<{ onClose: () => void }>) {
    const [file, setFile] = useState<File | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!file) return;
        const data = new FormData();
        data.append('file', file);
        setSubmitting(true);
        router.post(route('accounting.accounts.import'), data, {
            forceFormData: true,
            onSuccess: onClose,
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <button type="button" className="absolute inset-0 bg-black/50" onClick={onClose} aria-label="Fermer la fenêtre" />
            <div className="relative z-10 w-full max-w-lg rounded-lg bg-white shadow-2xl dark:bg-gray-800">
                <div className="border-b border-gray-200 p-5 dark:border-gray-700">
                    <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100">Importer le plan comptable (CSV)</h2>
                </div>
                <form onSubmit={submit} className="space-y-4 p-5">
                    <div>
                        <label htmlFor="accounts-import-file" className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fichier CSV
                        </label>
                        <input
                            id="accounts-import-file"
                            type="file"
                            accept=".csv,.txt"
                            onChange={(e) => setFile(e.target.files?.[0] ?? null)}
                            className="w-full text-sm text-gray-700 dark:text-gray-300"
                            required
                        />
                        <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Colonnes attendues (en-tête libre, insensible à la casse) : Numéro, Libellé, Classe (optionnel), Type (optionnel).
                            La classe est déduite du premier chiffre du numéro si elle est absente, et le type est déduit de la classe SYSCOHADA si absent.
                            Un compte dont le numéro existe déjà est mis à jour ; sinon il est créé.
                        </p>
                    </div>
                    <div className="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            disabled={!file || submitting}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {submitting ? 'Import en cours…' : 'Importer'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
