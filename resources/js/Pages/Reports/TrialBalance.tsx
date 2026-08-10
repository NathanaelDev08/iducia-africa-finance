import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';

interface Row {
    number: string;
    name: string;
    total_debit: number | string;
    total_credit: number | string;
}

export default function TrialBalance({ data = [] }: { data?: Row[] }) {
    const totalDebit = data.reduce((s, r) => s + parseFloat(String(r.total_debit)), 0);
    const totalCredit = data.reduce((s, r) => s + parseFloat(String(r.total_credit)), 0);

    return (
        <ErpLayout>
            <Head title="Balance Générale" />
            <div className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-center mb-4 p-3 bg-gray-50 rounded">
                    <span className="font-medium">Total Débit : <strong>{totalDebit.toLocaleString()} FCFA</strong></span>
                    <span className="font-medium">Total Crédit : <strong>{totalCredit.toLocaleString()} FCFA</strong></span>
                    <span className={'font-bold ' + (Math.abs(totalDebit - totalCredit) < 0.01 ? 'text-green-600' : 'text-red-600')}>
                        {Math.abs(totalDebit - totalCredit) < 0.01 ? '✓ Équilibrée' : '✗ Déséquilibrée'}
                    </span>
                </div>
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 border-b">
                            <th className="p-3 text-left text-gray-600">N° Compte</th>
                            <th className="p-3 text-left text-gray-600">Libellé</th>
                            <th className="p-3 text-right text-gray-600">Débit</th>
                            <th className="p-3 text-right text-gray-600">Crédit</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {data.map((row, i) => (
                            <tr key={i} className="hover:bg-gray-50">
                                <td className="p-3 font-mono">{row.number}</td>
                                <td className="p-3">{row.name}</td>
                                <td className="p-3 text-right font-mono">{parseFloat(String(row.total_debit)).toLocaleString()}</td>
                                <td className="p-3 text-right font-mono">{parseFloat(String(row.total_credit)).toLocaleString()}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </ErpLayout>
    );
}
