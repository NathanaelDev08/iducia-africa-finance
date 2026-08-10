import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';

interface PnlData { total_expenses?: number; total_revenues?: number; net_income?: number; }

export default function ProfitAndLoss({ data = {} }: { data?: PnlData }) {
    return (
        <ErpLayout>
            <Head title="Compte de Résultat" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div className="p-6 rounded-lg bg-white border-t-4 border-red-500 shadow-sm">
                    <h3 className="font-bold text-red-700 mb-2">Charges</h3>
                    <p className="text-3xl font-bold text-red-900">{(data.total_expenses || 0).toLocaleString()} FCFA</p>
                </div>
                <div className="p-6 rounded-lg bg-white border-t-4 border-green-500 shadow-sm">
                    <h3 className="font-bold text-green-700 mb-2">Produits</h3>
                    <p className="text-3xl font-bold text-green-900">{(data.total_revenues || 0).toLocaleString()} FCFA</p>
                </div>
            </div>
            <div className="p-6 bg-white rounded-lg shadow text-center">
                <p className="text-sm text-gray-500 mb-1 uppercase tracking-wide">Résultat Net</p>
                <p className={'text-4xl font-bold ' + ((data.net_income || 0) >= 0 ? 'text-green-700' : 'text-red-700')}>
                    {(data.net_income || 0).toLocaleString()} FCFA
                </p>
                <p className="text-sm text-gray-500 mt-2">{(data.net_income || 0) >= 0 ? 'Bénéfice' : 'Perte'}</p>
            </div>
        </ErpLayout>
    );
}
