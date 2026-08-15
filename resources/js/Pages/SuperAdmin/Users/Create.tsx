import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

interface Company { id: number; name: string; }
interface Module { id: number; code: string; name: string; icon?: string; }

export default function Create({ companies, modules, roles }: { companies: Company[]; modules: Module[]; roles: Record<string, string> }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        company_id: companies[0]?.id ?? '',
        role: 'employee',
        modules: [] as number[],
    });

    const toggleModule = (id: number) => {
        setData('modules', data.modules.includes(id) ? data.modules.filter((m) => m !== id) : [...data.modules, id]);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/super-admin/users');
    };

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Nouvel utilisateur" />
            <div className="mx-auto max-w-2xl py-8 px-6">
                <h1 className="mb-1 text-2xl font-bold">👤 Créer un utilisateur</h1>
                <p className="mb-6 text-sm text-gray-500">
                    Un mot de passe temporaire est généré automatiquement et envoyé par email.
                    L'utilisateur devra le changer à sa première connexion.
                </p>

                <form onSubmit={submit} className="space-y-6 rounded-lg bg-white p-6 shadow">
                    <div>
                        <label className="block text-sm font-medium">Nom complet *</label>
                        <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2" />
                        {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Email *</label>
                        <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2" />
                        {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Entreprise *</label>
                        <select value={data.company_id} onChange={(e) => setData('company_id', Number(e.target.value))} required className="mt-1 w-full rounded border px-3 py-2">
                            {companies.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                        {errors.company_id && <p className="text-sm text-red-600">{errors.company_id}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Rôle *</label>
                        <select value={data.role} onChange={(e) => setData('role', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2">
                            {Object.entries(roles).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                        </select>
                        {errors.role && <p className="text-sm text-red-600">{errors.role}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Modules accessibles *</label>
                        <div className="mt-2 grid grid-cols-2 gap-2 rounded border p-3">
                            {modules.map((m) => (
                                <label key={m.id} className="flex items-center gap-2 text-sm">
                                    <input type="checkbox" checked={data.modules.includes(m.id)} onChange={() => toggleModule(m.id)} />
                                    {m.icon} {m.name}
                                </label>
                            ))}
                        </div>
                        {errors.modules && <p className="text-sm text-red-600">{errors.modules}</p>}
                    </div>

                    <div className="flex justify-end space-x-2 border-t pt-4">
                        <a href="/super-admin/users" className="rounded border px-4 py-2">Annuler</a>
                        <button type="submit" disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">
                            Créer et envoyer les identifiants
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
