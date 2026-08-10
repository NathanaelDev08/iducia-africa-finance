import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ auth, stats, recentCompanies }: any) {
    const cards = [
        { label: 'Entreprises', value: stats.companies_total, sub: `${stats.companies_active} actives`, color: 'bg-blue-500', link: '/super-admin/companies' },
        { label: 'Utilisateurs', value: stats.users_total, sub: 'tous rôles', color: 'bg-indigo-500', link: '/super-admin/users' },
        { label: 'Employés', value: stats.employees_total, sub: 'toutes entreprises', color: 'bg-green-500', link: '/super-admin/companies' },
        { label: 'Bulletins de paie', value: stats.payslips_total, sub: 'générés', color: 'bg-purple-500', link: '/super-admin/companies' },
        { label: 'Factures', value: stats.invoices_total, sub: 'ventes', color: 'bg-orange-500', link: '/super-admin/companies' },
    ];

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Super Admin - Dashboard" />
            <div className="py-8 px-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-800">🛡️ Administration Plateforme</h1>
                    <div className="space-x-2">
                        <Link href="/super-admin/companies/create" className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            + Nouvelle entreprise
                        </Link>
                        <Link href="/super-admin/users/create" className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                            + Nouvel utilisateur
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    {cards.map((card) => (
                        <Link key={card.label} href={card.link} className={`rounded-lg ${card.color} p-5 text-white shadow hover:opacity-90`}>
                            <div className="text-3xl font-bold">{card.value}</div>
                            <div className="text-sm opacity-90">{card.label}</div>
                            <div className="text-xs opacity-75">{card.sub}</div>
                        </Link>
                    ))}
                </div>

                <div className="mt-8 rounded-lg bg-white p-6 shadow">
                    <h2 className="mb-4 text-lg font-semibold">Entreprises récentes</h2>
                    <table className="w-full">
                        <thead>
                            <tr className="border-b text-left text-sm text-gray-500">
                                <th className="pb-2">Nom</th>
                                <th className="pb-2">Devise</th>
                                <th className="pb-2">Statut</th>
                                <th className="pb-2">Créée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentCompanies.map((c: any) => (
                                <tr key={c.id} className="border-b hover:bg-gray-50">
                                    <td className="py-2">
                                        <Link href={`/super-admin/companies/${c.id}`} className="text-blue-600 hover:underline">{c.name}</Link>
                                    </td>
                                    <td className="py-2">{c.currency}</td>
                                    <td className="py-2">
                                        <span className={`rounded px-2 py-1 text-xs ${c.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                            {c.is_active ? 'Active' : 'Suspendue'}
                                        </span>
                                    </td>
                                    <td className="py-2 text-sm text-gray-500">{new Date(c.created_at).toLocaleDateString('fr-FR')}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
