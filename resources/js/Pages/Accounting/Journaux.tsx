import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import { FileText, BookOpen, Notebook, Scale, List, Pencil, Trash2, Plus } from 'lucide-react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Journal { id: number; code: string; name: string; type: string; is_active: boolean; }

const JOURNAL_TYPES = [
    { value: 'sale', label: 'Ventes' },
    { value: 'purchase', label: 'Achats' },
    { value: 'bank', label: 'Banque' },
    { value: 'cash', label: 'Caisse' },
    { value: 'payroll', label: 'Paie' },
    { value: 'misc', label: 'Opérations diverses' },
];

function JournalModal({ journal, onClose }: { journal: Journal | null; onClose: () => void }) {
    const { data, setData, post, put, processing, errors } = useForm({
        code: journal?.code || '',
        name: journal?.name || '',
        type: journal?.type || 'misc',
        is_active: journal?.is_active ?? true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (journal) {
            put(route('accounting.journals.update', journal.id), { onSuccess: onClose });
        } else {
            post(route('accounting.journals.store'), { onSuccess: onClose });
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-md w-full" onClick={(e) => e.stopPropagation()}>
                <form onSubmit={submit}>
                    <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 className="text-lg font-bold text-gray-900 dark:text-gray-100">{journal ? 'Modifier le journal' : 'Nouveau journal'}</h2>
                    </div>
                    <div className="p-6 space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                            <input type="text" value={data.code} onChange={(e) => setData('code', e.target.value.toUpperCase())} maxLength={10} required
                                className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm font-mono" />
                            {errors.code && <p className="text-xs text-red-600 mt-1">{errors.code}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required
                                className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm" />
                            {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
                        </div>
                        {!journal && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                                <select value={data.type} onChange={(e) => setData('type', e.target.value)}
                                    className="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    {JOURNAL_TYPES.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
                                </select>
                            </div>
                        )}
                        {journal && (
                            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                                Journal actif
                            </label>
                        )}
                    </div>
                    <div className="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex justify-end gap-3 rounded-b-lg">
                        <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">Annuler</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-brand-navy rounded-md hover:opacity-90 disabled:opacity-50">
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function Journaux({ journals, activeTab }: PageProps<{ journals: Journal[], activeTab: string }>) {
    const [view, setView] = useState<ViewMode>('list');
    const [modalJournal, setModalJournal] = useState<Journal | null | undefined>(undefined);

    const deleteJournal = (journal: Journal) => {
        if (confirm(`Supprimer le journal "${journal.name}" ?`)) {
            router.delete(route('accounting.journals.destroy', journal.id));
        }
    };

    const tabs = [
        { label: 'Écritures', href: route('accounting.index'), active: activeTab === 'ecritures', icon: FileText },
        { label: 'Plan Comptable', href: route('accounting.plan'), active: activeTab === 'plan', icon: List },
        { label: 'Journaux', href: route('accounting.journals'), active: activeTab === 'journaux', icon: Notebook },
        { label: 'Balance', href: route('accounting.balance'), active: activeTab === 'balance', icon: Scale },
        { label: 'Grand Livre', href: route('accounting.grand-livre'), active: activeTab === 'grand-livre', icon: BookOpen },
    ];

    return (
        <ErpLayout>
            <Head title="Journaux" />
            <div className="py-2">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Journaux Comptables</h1>
                    <div className="flex items-center gap-3">
                        <ViewSwitcher value={view} onChange={setView} />
                        <button onClick={() => setModalJournal(null)} className="inline-flex items-center gap-1.5 bg-brand-navy hover:opacity-90 text-white text-sm font-medium px-4 py-2 rounded-lg">
                            <Plus className="h-4 w-4" /> Nouveau journal
                        </button>
                    </div>
                </div>
                <Tabs tabs={tabs} />
                {view === 'list' ? (
                <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nom</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Statut</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {journals.length === 0 ? (
                                <tr><td colSpan={5} className="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Aucun journal.</td></tr>
                            ) : (
                                journals.map((journal) => (
                                    <tr key={journal.id} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{journal.code}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{journal.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{journal.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${journal.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                                {journal.is_active ? 'Actif' : 'Inactif'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right">
                                            <div className="flex items-center justify-end gap-3">
                                                <button onClick={() => setModalJournal(journal)} title="Modifier" className="text-gray-400 hover:text-brand-navy"><Pencil className="h-4 w-4" /></button>
                                                <button onClick={() => deleteJournal(journal)} title="Supprimer" className="text-gray-400 hover:text-red-600"><Trash2 className="h-4 w-4" /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                ) : (
                <KanbanBoard
                    data={journals}
                    rowKey={(j) => j.id}
                    groupBy={(j) => (j.is_active ? 'active' : 'inactive')}
                    columns={[
                        { key: 'active', label: 'Actifs', colorClass: 'bg-green-100 text-green-800' },
                        { key: 'inactive', label: 'Inactifs', colorClass: 'bg-red-100 text-red-800' },
                    ]}
                    emptyMessage="Aucun journal."
                    renderCard={(journal) => (
                        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div className="font-mono text-xs text-gray-500 dark:text-gray-400">{journal.code}</div>
                            <div className="font-medium text-gray-900 dark:text-gray-100">{journal.name}</div>
                            <div className="mt-1 text-xs text-gray-500 dark:text-gray-300">{journal.type}</div>
                            <div className="mt-3 flex gap-3 border-t border-gray-100 dark:border-gray-700 pt-2 text-xs">
                                <button onClick={() => setModalJournal(journal)} className="text-brand-navy hover:underline">Modifier</button>
                                <button onClick={() => deleteJournal(journal)} className="text-red-600 hover:underline">Supprimer</button>
                            </div>
                        </div>
                    )}
                />
                )}
            </div>
            {modalJournal !== undefined && <JournalModal journal={modalJournal} onClose={() => setModalJournal(undefined)} />}
        </ErpLayout>
    );
}
