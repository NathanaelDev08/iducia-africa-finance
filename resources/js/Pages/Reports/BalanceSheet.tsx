import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';

interface BsData { total_assets?: number; total_liabilities?: number; }

export default function BalanceSheet({ data = {} }: { data?: BsData }) {
    return (
        <ErpLayout>
            <Head title="Bilan Comptable" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="p-6 rounded-lg bg-white border-t-4 border-blue-500 shadow-sm">
                    <h3 className="font-bold text-blue-800 text-lg mb-4">🏛️ ACTIF</h3>
                    <p className="text-3xl font-bold text-blue-900">{(data.total_assets || 0).toLocaleString()} FCFA</p>
                </div>
                <div className="p-6 rounded-lg bg-white border-t-4 border-purple-500 shadow-sm">
                    <h3 className="font-bold text-purple-800 text-lg mb-4">💼 PASSIF</h3>
                    <p className="text-3xl font-bold text-purple-900">{(data.total_liabilities || 0).toLocaleString()} FCFA</p>
                </div>
            </div>
        </ErpLayout>
    );
}
