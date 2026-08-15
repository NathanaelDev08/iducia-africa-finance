import { useState } from 'react';
import ErpLayout from '@/Layouts/ErpLayout';
import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import Tabs from '@/Components/Tabs';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SelectInput from '@/Components/SelectInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { FileText, BookOpen, Notebook, Scale, List, Plus, Trash2 } from 'lucide-react';

interface Journal { id: number; code: string; name: string; }
interface Account { id: number; number: string; name: string; }

interface EntryLine {
    account_id: number | '';
    description: string;
    debit: number | '';
    credit: number | '';
}

interface Props extends PageProps {
    journals: Journal[];
    accounts: Account[];
    activeTab: string;
}

export default function CreateEcriture({ journals, accounts, activeTab, errors }: Props) {
    const tabs = [
        { label: 'Écritures', href: route('accounting.index'), active: activeTab === 'ecritures', icon: FileText },
        { label: 'Plan Comptable', href: route('accounting.plan'), active: activeTab === 'plan', icon: List },
        { label: 'Journaux', href: route('accounting.journals'), active: activeTab === 'journaux', icon: Notebook },
        { label: 'Balance', href: route('accounting.balance'), active: activeTab === 'balance', icon: Scale },
        { label: 'Grand Livre', href: route('accounting.grand-livre'), active: activeTab === 'grand-livre', icon: BookOpen },
    ];

    const initialLines: EntryLine[] = [
        { account_id: '', description: '', debit: '', credit: '' },
        { account_id: '', description: '', debit: '', credit: '' },
    ];

    const { data, setData, post, processing, reset } = useForm({
        journal_id: '',
        entry_date: new Date().toISOString().split('T')[0],
        reference: '',
        description: '',
        lines: initialLines,
        validate_immediately: false,
    });

    const totalDebit = data.lines.reduce((sum, line) => sum + (parseFloat(String(line.debit)) || 0), 0);
    const totalCredit = data.lines.reduce((sum, line) => sum + (parseFloat(String(line.credit)) || 0), 0);
    const isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;

    const updateLine = (index: number, field: keyof EntryLine, value: any) => {
        const newLines = [...data.lines];
        newLines[index] = { ...newLines[index], [field]: value };
        setData('lines', newLines);
    };

    const addLine = () => {
        setData('lines', [...data.lines, { account_id: '', description: '', debit: '', credit: '' }]);
    };

    const removeLine = (index: number) => {
        if (data.lines.length <= 2) return;
        setData('lines', data.lines.filter((_, i) => i !== index));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('accounting.ecritures.store'));
    };

    return (
        <ErpLayout>
            <Head title="Nouvelle Écriture" />
            <div className="py-2">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Nouvelle Écriture Comptable</h1>
                <Tabs tabs={tabs} />

                <form onSubmit={handleSubmit} className="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    {/* Informations générales */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel required>Journal</InputLabel>
                            <SelectInput
                                className="w-full"
                                value={data.journal_id}
                                onChange={(e) => setData('journal_id', e.target.value)}
                                options={journals.map(j => ({ value: j.id, label: `${j.code} - ${j.name}` }))}
                                placeholder="Sélectionner un journal"
                            />
                            <InputError message={errors.journal_id} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel required>Date de l'écriture</InputLabel>
                            <TextInput
                                type="date"
                                className="w-full"
                                value={data.entry_date}
                                onChange={(e) => setData('entry_date', e.target.value)}
                            />
                            <InputError message={errors.entry_date} className="mt-1" />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <InputLabel>Référence</InputLabel>
                            <TextInput
                                type="text"
                                className="w-full"
                                value={data.reference}
                                onChange={(e) => setData('reference', e.target.value)}
                                placeholder="Ex: FAC-2026-001"
                            />
                            <InputError message={errors.reference} className="mt-1" />
                        </div>
                        <div>
                            <InputLabel required>Libellé</InputLabel>
                            <TextInput
                                type="text"
                                className="w-full"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                placeholder="Description de l'écriture"
                            />
                            <InputError message={errors.description} className="mt-1" />
                        </div>
                    </div>

                    {/* Lignes d'écriture */}
                    <div className="mb-4">
                        <div className="flex justify-between items-center mb-3">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">Lignes d'écriture</h3>
                            <SecondaryButton type="button" onClick={addLine}>
                                <Plus className="h-4 w-4 mr-1" /> Ajouter une ligne
                            </SecondaryButton>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded">
                                <thead className="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/3">Compte</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/4">Libellé</th>
                                        <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/6">Débit</th>
                                        <th className="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/6">Crédit</th>
                                        <th className="px-3 py-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    {data.lines.map((line, index) => (
                                        <tr key={index}>
                                            <td className="px-3 py-2">
                                                <SelectInput
                                                    className="w-full"
                                                    value={line.account_id}
                                                    onChange={(e) => updateLine(index, 'account_id', e.target.value)}
                                                    options={accounts.map(a => ({ value: a.id, label: `${a.number} - ${a.name}` }))}
                                                    placeholder="Sélectionner"
                                                />
                                                <InputError message={errors[`lines.${index}.account_id`] as string} className="mt-1 text-xs" />
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    type="text"
                                                    className="w-full"
                                                    value={line.description}
                                                    onChange={(e) => updateLine(index, 'description', e.target.value)}
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    className="w-full text-right"
                                                    value={line.debit}
                                                    onChange={(e) => updateLine(index, 'debit', e.target.value)}
                                                />
                                                <InputError message={errors[`lines.${index}.debit`] as string} className="mt-1 text-xs" />
                                            </td>
                                            <td className="px-3 py-2">
                                                <TextInput
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    className="w-full text-right"
                                                    value={line.credit}
                                                    onChange={(e) => updateLine(index, 'credit', e.target.value)}
                                                />
                                                <InputError message={errors[`lines.${index}.credit`] as string} className="mt-1 text-xs" />
                                            </td>
                                            <td className="px-3 py-2 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() => removeLine(index)}
                                                    disabled={data.lines.length <= 2}
                                                    className="text-red-500 hover:text-red-700 disabled:opacity-50"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot className="bg-gray-50 dark:bg-gray-700 font-semibold">
                                    <tr>
                                        <td colSpan={2} className="px-3 py-2 text-right text-sm text-gray-900 dark:text-gray-100">Totaux :</td>
                                        <td className="px-3 py-2 text-right text-sm text-gray-900 dark:text-gray-100">{totalDebit.toLocaleString('fr-FR', { minimumFractionDigits: 2 })}</td>
                                        <td className="px-3 py-2 text-right text-sm text-gray-900 dark:text-gray-100">{totalCredit.toLocaleString('fr-FR', { minimumFractionDigits: 2 })}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {/* Indicateur d'équilibre */}
                        <div className={`mt-3 p-3 rounded-md ${isBalanced ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200'}`}>
                            {isBalanced
                                ? '✓ L\'écriture est équilibrée'
                                : `✗ L'écriture n'est pas équilibrée (écart : ${Math.abs(totalDebit - totalCredit).toLocaleString('fr-FR', { minimumFractionDigits: 2 })} FCFA)`
                            }
                        </div>

                        <InputError message={errors.lines} className="mt-2" />
                    </div>

                    {/* Options et boutons */}
                    <div className="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                checked={data.validate_immediately}
                                onChange={(e) => setData('validate_immediately', e.target.checked)}
                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span className="ml-2 text-sm text-gray-600 dark:text-gray-400">Valider immédiatement l'écriture</span>
                        </label>

                        <div className="flex space-x-3">
                            <SecondaryButton type="button" onClick={() => reset()}>
                                Réinitialiser
                            </SecondaryButton>
                            <PrimaryButton disabled={processing || !isBalanced}>
                                {processing ? 'Enregistrement...' : 'Enregistrer'}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </ErpLayout>
    );
}
