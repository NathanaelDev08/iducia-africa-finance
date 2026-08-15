import ErpLayout from '@/Layouts/ErpLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth }: any) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        short_name: '',
        email: '',
        phone: '',
        address: '',
        rccm: '',
        tax_id: '',
        currency: 'XOF',
        timezone: 'Africa/Abidjan',
        admin_name: '',
        admin_email: '',
        admin_password: '',
    });

    const submit = (e: any) => {
        e.preventDefault();
        post('/super-admin/companies');
    };

    return (
        <ErpLayout>
            <Head title="Nouvelle entreprise" />
            <div className="mx-auto max-w-3xl py-8">
                <h1 className="mb-6 text-2xl font-bold">🏢 Créer une nouvelle entreprise</h1>

                <form onSubmit={submit} className="space-y-6 rounded-lg bg-white p-6 shadow">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <label className="block text-sm font-medium">Nom de l'entreprise *</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2" />
                            {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Nom abrégé</label>
                            <input type="text" value={data.short_name} onChange={(e) => setData('short_name', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Devise *</label>
                            <select value={data.currency} onChange={(e) => setData('currency', e.target.value)} className="mt-1 w-full rounded border px-3 py-2">
                                <option value="XOF">XOF - Franc CFA</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="USD">USD - Dollar</option>
                                <option value="GBP">GBP - Livre</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Email</label>
                            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Téléphone</label>
                            <input type="text" value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">RCCM</label>
                            <input type="text" value={data.rccm} onChange={(e) => setData('rccm', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">N° Fiscal</label>
                            <input type="text" value={data.tax_id} onChange={(e) => setData('tax_id', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div className="col-span-2">
                            <label className="block text-sm font-medium">Adresse</label>
                            <textarea value={data.address} onChange={(e) => setData('address', e.target.value)} rows={2} className="mt-1 w-full rounded border px-3 py-2"></textarea>
                        </div>
                    </div>

                    <div className="border-t pt-4">
                        <h3 className="mb-3 font-semibold">👤 Administrateur de l'entreprise</h3>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="col-span-2">
                                <label className="block text-sm font-medium">Nom complet *</label>
                                <input type="text" value={data.admin_name} onChange={(e) => setData('admin_name', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2" />
                                {errors.admin_name && <p className="text-sm text-red-600">{errors.admin_name}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium">Email *</label>
                                <input type="email" value={data.admin_email} onChange={(e) => setData('admin_email', e.target.value)} required className="mt-1 w-full rounded border px-3 py-2" />
                                {errors.admin_email && <p className="text-sm text-red-600">{errors.admin_email}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium">Mot de passe * (min 8 car.)</label>
                                <input type="password" value={data.admin_password} onChange={(e) => setData('admin_password', e.target.value)} required minLength={8} className="mt-1 w-full rounded border px-3 py-2" />
                                {errors.admin_password && <p className="text-sm text-red-600">{errors.admin_password}</p>}
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end space-x-2">
                        <a href="/super-admin/companies" className="rounded border px-4 py-2">Annuler</a>
                        <button type="submit" disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50">
                            Créer l'entreprise
                        </button>
                    </div>
                </form>
            </div>
        </ErpLayout>
    );
}
