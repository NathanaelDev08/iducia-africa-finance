import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, users, roles, filters }: any) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: any) => {
        e.preventDefault();
        router.get('/super-admin/users', { search }, { preserveState: true });
    };

    const resetPassword = (user: any) => {
        if (confirm(`Réinitialiser le mot de passe de ${user.name} ?`)) {
            router.post(`/super-admin/users/${user.id}/reset-password`);
        }
    };

    const deleteUser = (user: any) => {
        if (confirm(`Supprimer l'utilisateur "${user.name}" ?`)) {
            router.delete(`/super-admin/users/${user.id}`);
        }
    };

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Utilisateurs - Super Admin" />
            <div className="py-8 px-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">👥 Gestion des Utilisateurs</h1>
                    <Link href="/super-admin/users/create" className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                        + Nouvel utilisateur
                    </Link>
                </div>

                <form onSubmit={handleSearch} className="mb-4">
                    <input type="text" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Rechercher..." className="w-full rounded border px-4 py-2 md:w-1/3" />
                </form>

                <div className="overflow-hidden rounded-lg bg-white shadow">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                            <tr className="text-left text-sm text-gray-600">
                                <th className="px-4 py-3">Nom</th>
                                <th className="px-4 py-3">Email</th>
                                <th className="px-4 py-3">Entreprises</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((u: any) => (
                                <tr key={u.id} className="border-t hover:bg-gray-50">
                                    <td className="px-4 py-3 font-medium">{u.name}</td>
                                    <td className="px-4 py-3">{u.email}</td>
                                    <td className="px-4 py-3">
                                        {u.companies.map((c: any) => (
                                            <span key={c.id} className="mr-1 rounded bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                                {c.name} ({c.pivot.role})
                                            </span>
                                        ))}
                                    </td>
                                    <td className="px-4 py-3 space-x-2">
                                        <Link href={`/super-admin/users/${u.id}/edit`} className="text-blue-600 hover:underline">Éditer</Link>
                                        <button onClick={() => resetPassword(u)} className="text-orange-600 hover:underline">Reset MDP</button>
                                        <button onClick={() => deleteUser(u)} className="text-red-600 hover:underline">Supprimer</button>
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
