import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Index({ auth }: PageProps) {
    const reports = [
        { title: 'Balance Générale', description: 'Équilibre Débit/Crédit de tous les comptes', route: 'reporting.trial-balance', icon: '⚖️' },
        { title: 'Compte de Résultat', description: 'Produits, Charges et Résultat net', route: 'reporting.profit-and-loss', icon: '📈' },
        { title: 'Bilan', description: 'Actif et Passif de l\'entreprise', route: 'reporting.balance-sheet', icon: '🏦' },
    ];

    return (
        <ErpLayout>
            <Head title="Reporting Financier" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 className="text-2xl font-bold mb-6">États Financiers</h1>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {reports.map((report) => (
                            <Link key={report.route} href={route(report.route)} className="block">
                                <div className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border border-gray-100">
                                    <div className="text-3xl mb-4">{report.icon}</div>
                                    <h3 className="text-lg font-semibold text-gray-900">{report.title}</h3>
                                    <p className="text-sm text-gray-500 mt-2">{report.description}</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}
