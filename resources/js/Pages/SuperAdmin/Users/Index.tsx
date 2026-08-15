import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Pivot { role?: string; is_active?: boolean; }
interface Company { id: number; name: string; pivot?: Pivot; }
interface ModuleRow { id: number; code: string; name: string; icon?: string; }
interface UserRow {
    id: number; name: string; email: string; is_active: boolean;
    companies: Company[]; modules: ModuleRow[];
}

export default function Index({ users, roles }: { users: UserRow[]; companies: any[]; modules: any[]; roles: Record<string, string> }) {
    const [view, setView] = useState<ViewMode>('list');

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
        <ErpLayout>
            <Head title="Utilisateurs - Super Admin" />
            <div className="py-8 px-6">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">👤 Gestion des Utilisateurs</h1>
                    <div className="flex items-center gap-3">
                        <ViewSwitcher value={view} onChange={setView} />
                        <Link href="/super-admin/users/create" className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            + Nouvel utilisateur
                        </Link>
                    </div>
                </div>

                {view === 'list' ? (
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
                ) : (
                    <KanbanBoard
                        data={users}
                        rowKey={(u) => u.id}
                        groupBy={(u) => (u.is_active ? 'active' : 'inactive')}
                        columns={[
                            { key: 'active', label: '● Actifs', colorClass: 'bg-green-100 text-green-800' },
                            { key: 'inactive', label: '● Désactivés', colorClass: 'bg-red-100 text-red-800' },
                        ]}
                        emptyMessage="Aucun utilisateur"
                        renderCard={(u) => (
                            <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                                <div className="font-medium text-gray-900">{u.name}</div>
                                <div className="text-xs text-gray-500">{u.email}</div>
                                <div className="mt-2 text-xs text-gray-500">
                                    {u.companies.map((c) => c.name).join(', ') || '—'} · {roles[u.companies[0]?.pivot?.role ?? ''] ?? u.companies[0]?.pivot?.role ?? '—'}
                                </div>
                                <div className="mt-2 flex flex-wrap gap-1">
                                    {u.modules.length ? u.modules.map((m) => (
                                        <span key={m.id} className="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{m.icon} {m.name}</span>
                                    )) : <span className="text-xs text-gray-400">aucun</span>}
                                </div>
                                <div className="mt-3 flex gap-3 border-t border-gray-100 pt-2 text-xs">
                                    <button onClick={() => resetPassword(u)} className="text-blue-600 hover:underline">Réinitialiser MDP</button>
                                    <button onClick={() => toggle(u)} className={u.is_active ? 'text-orange-600 hover:underline' : 'text-green-600 hover:underline'}>
                                        {u.is_active ? 'Désactiver' : 'Réactiver'}
                                    </button>
                                </div>
                            </div>
                        )}
                    />
                )}
            </div>
        </ErpLayout>
    );
}
