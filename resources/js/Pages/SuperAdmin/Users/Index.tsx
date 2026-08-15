import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

interface Pivot { role?: string; is_active?: boolean; }
interface Company { id: number; name: string; pivot?: Pivot; }
interface ModuleRow { id: number; code: string; name: string; icon?: string; }
interface UserRow {
    id: number; name: string; email: string; is_active: boolean;
    companies: Company[]; modules: ModuleRow[];
}

export default function Index({ users, roles }: { users: UserRow[]; companies: any[]; modules: any[]; roles: Record<string, string> }) {
    const flash = (usePage().props as any).flash;

    const resetPassword = (u: UserRow) => {
        if (confirm(`Réinitialiser le mot de passe de ${u.name} et lui renvoyer un email ?`)) {
            router.post(`/super-admin/users/${u.id}/reset-password`);
        }
    };

    const toggle = (u: UserRow) => {
        if (confirm(u.is_active ? `Désactiver le compte de ${u.name} ?` : `Réactiver le compte de ${u.name} ?`)) {
            router.post(`/super-admin/users/${u.id}/toggle`);
        }
    };

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Utilisateurs - Super Admin" />
            <div className="py-8 px-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">👤 Gestion des Utilisateurs</h1>
                    <Link href="/super-admin/users/create" className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        + Nouvel utilisateur
                    </Link>
                </div>

                {flash?.success && <div className="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-800">✓ {flash.success}</div>}
                {flash?.error && <div className="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">✗ {flash.error}</div>}

                <div className="overflow-hidden rounded-lg bg-white shadow">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50">
                            <tr className="text-left text-gray-600">
                                <th className="px-4 py-3">Nom</th>
                                <th className="px-4 py-3">Email</th>
                                <th className="px-4 py-3">Entreprise</th>
                                <th className="px-4 py-3">Rôle</th>
                                <th className="px-4 py-3">Modules</th>
                                <th className="px-4 py-3">Statut</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((u) => (
                                <tr key={u.id} className="border-t hover:bg-gray-50">
                                    <td className="px-4 py-3 font-medium">{u.name}</td>
                                    <td className="px-4 py-3 text-gray-600">{u.email}</td>
                                    <td className="px-4 py-3">{u.companies.map((c) => c.name).join(', ') || '—'}</td>
                                    <td className="px-4 py-3">{roles[u.companies[0]?.pivot?.role ?? ''] ?? u.companies[0]?.pivot?.role ?? '—'}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-1">
                                            {u.modules.length ? u.modules.map((m) => (
                                                <span key={m.id} className="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{m.icon} {m.name}</span>
                                            )) : <span className="text-gray-400">aucun</span>}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`rounded px-2 py-1 text-xs ${u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                            {u.is_active ? 'Actif' : 'Désactivé'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 space-x-2 whitespace-nowrap">
                                        <button onClick={() => resetPassword(u)} className="text-blue-600 hover:underline">Réinitialiser MDP</button>
                                        <button onClick={() => toggle(u)} className={u.is_active ? 'text-orange-600 hover:underline' : 'text-green-600 hover:underline'}>
                                            {u.is_active ? 'Désactiver' : 'Réactiver'}
                                        </button>
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
