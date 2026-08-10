import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, companies, filters }: any) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: any) => {
        e.preventDefault();
        router.get('/super-admin/companies', { search }, { preserveState: true });
    };

    const toggleActive = (company: any) => {
        if (confirm(company.is_active ? `Suspendre "${company.name}" ?` : `Réactiver "${company.name}" ?`)) {
            router.post(`/super-admin/companies/${company.id}/toggle-active`);
        }
    };

    const deleteCompany = (company: any) => {
        if (confirm(`⚠️ ATTENTION : Supprimer "${company.name}" effacera TOUTES ses données (employés, bulletins, factures...). Continuer ?`)) {
            router.delete(`/super-admin/companies/${company.id}`);
        }
    };

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Entreprises - Super Admin" />
            <div className="py-8 px-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">🏢 Gestion des Entreprises</h1>
                    <Link href="/super-admin/companies/create" className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        + Nouvelle entreprise
                    </Link>
                </div>

                <form onSubmit={handleSearch} className="mb-4">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Rechercher une entreprise..."
                        className="w-full rounded border px-4 py-2 md:w-1/3"
                    />
                </form>

                <div className="overflow-hidden rounded-lg bg-white shadow">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                            <tr className="text-left text-sm text-gray-600">
                                <th className="px-4 py-3">Entreprise</th>
                                <th className="px-4 py-3">Devise</th>
                                <th className="px-4 py-3">Utilisateurs</th>
                                <th className="px-4 py-3">Employés</th>
                                <th className="px-4 py-3">Statut</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {companies.data.map((c: any) => (
                                <tr key={c.id} className="border-t hover:bg-gray-50">
                                    <td className="px-4 py-3">
                                        <Link href={`/super-admin/companies/${c.id}`} className="font-medium text-blue-600 hover:underline">
                                            {c.name}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-3">{c.currency}</td>
                                    <td className="px-4 py-3">{c.users_count || 0}</td>
                                    <td className="px-4 py-3">{c.employees_count || 0}</td>
                                    <td className="px-4 py-3">
                                        <span className={`rounded px-2 py-1 text-xs ${c.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                            {c.is_active ? 'Active' : 'Suspendue'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 space-x-2">
                                        <Link href={`/super-admin/companies/${c.id}/edit`} className="text-blue-600 hover:underline">Éditer</Link>
                                        <button onClick={() => toggleActive(c)} className={c.is_active ? 'text-orange-600' : 'text-green-600'}>
                                            {c.is_active ? 'Suspendre' : 'Réactiver'}
                                        </button>
                                        <button onClick={() => deleteCompany(c)} className="text-red-600 hover:underline">Supprimer</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
