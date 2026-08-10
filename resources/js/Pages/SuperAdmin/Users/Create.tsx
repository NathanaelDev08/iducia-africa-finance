import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, companies, roles }: any) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'employee',
        company_id: companies[0]?.id || '',
    });

    const submit = (e: any) => {
        e.preventDefault();
        post('/super-admin/users');
    };

    return (
        <AuthenticatedLayout header={<></>}>
            <Head title="Nouvel utilisateur" />
            <div className="mx-auto max-w-2xl py-8">
                <h1 className="mb-6 text-2xl font-bold">👤 Créer un utilisateur</h1>

                <form onSubmit={submit} className="space-y-4 rounded-lg bg-white p-6 shadow">
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
                        <label className="block text-sm font-medium">Mot de passe * (min 8 car.)</label>
                        <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required minLength={8} className="mt-1 w-full rounded border px-3 py-2" />
                        {errors.password && <p className="text-sm text-red-600">{errors.password}</p>}
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Entreprise *</label>
                            <select value={data.company_id} onChange={(e) => setData('company_id', e.target.value)} className="mt-1 w-full rounded border px-3 py-2">
                                {companies.map((c: any) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Rôle *</label>
                            <select value={data.role} onChange={(e) => setData('role', e.target.value)} className="mt-1 w-full rounded border px-3 py-2">
                                {Object.entries(roles).map(([key, label]) => (
                                    <option key={key} value={key}>{label as string}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div className="flex justify-end space-x-2">
                        <a href="/super-admin/users" className="rounded border px-4 py-2">Annuler</a>
                        <button type="submit" disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50">
                            Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
