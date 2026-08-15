import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';

interface PnlRow { number: string; name: string; total_debit: number; total_credit: number; }
interface PnlData {
    expenses: PnlRow[];
    revenues: PnlRow[];
    total_expenses: number;
    total_revenues: number;
    net_income: number;
}

export default function ProfitAndLoss({ auth, data }: PageProps<{ data: PnlData }>) {
    return (
        <ErpLayout>
            <Head title="Compte de Résultat" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-md rounded-lg p-6">
                        <h2 className="text-xl font-bold mb-6 text-center">Compte de Résultat</h2>
                        
                        <div className="mb-8">
                            <h3 className="font-bold text-lg border-b pb-2 mb-2 text-red-600">Charges</h3>
                            {data.expenses.map((row) => (
                                <div key={row.number} className="flex justify-between py-1 text-sm">
                                    <span>{row.number} - {row.name}</span>
                                    <span>{(row.total_debit - row.total_credit).toLocaleString()} FCFA</span>
                                </div>
                            ))}
                            <div className="flex justify-between font-bold border-t mt-2 pt-2">
                                <span>Total Charges</span>
                                <span>{data.total_expenses.toLocaleString()} FCFA</span>
                            </div>
                        </div>

                        <div className="mb-8">
                            <h3 className="font-bold text-lg border-b pb-2 mb-2 text-green-600">Produits</h3>
                            {data.revenues.map((row) => (
                                <div key={row.number} className="flex justify-between py-1 text-sm">
                                    <span>{row.number} - {row.name}</span>
                                    <span>{(row.total_credit - row.total_debit).toLocaleString()} FCFA</span>
                                </div>
                            ))}
                            <div className="flex justify-between font-bold border-t mt-2 pt-2">
                                <span>Total Produits</span>
                                <span>{data.total_revenues.toLocaleString()} FCFA</span>
                            </div>
                        </div>

                        <div className={`p-4 rounded-lg ${data.net_income >= 0 ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                            <div className="flex justify-between font-bold text-lg">
                                <span>RÉSULTAT NET</span>
                                <span>{data.net_income.toLocaleString()} FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}
