import ErpLayout from '@/Layouts/ErpLayout';
import { Head, useForm } from '@inertiajs/react';

interface Company {
    id: number; name: string; short_name: string | null; email: string | null; phone: string | null;
    address: string | null; rccm: string | null; tax_id: string | null; currency: string; timezone: string;
}

export default function Edit({ company }: { company: Company }) {
    const { data, setData, put, processing, errors } = useForm({
        name: company.name,
        short_name: company.short_name || '',
        email: company.email || '',
        phone: company.phone || '',
        address: company.address || '',
        rccm: company.rccm || '',
        tax_id: company.tax_id || '',
        currency: company.currency,
        timezone: company.timezone,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/super-admin/companies/${company.id}`);
    };

    return (
        <ErpLayout>
            <Head title={`Éditer ${company.name}`} />
            <div className="mx-auto max-w-3xl py-8">
                <h1 className="mb-6 text-2xl font-bold">✏️ Éditer {company.name}</h1>

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
                        <div>
                            <label className="block text-sm font-medium">Fuseau horaire</label>
                            <input type="text" value={data.timezone} onChange={(e) => setData('timezone', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                    </div>

                    <div className="flex justify-end space-x-2">
                        <a href={`/super-admin/companies/${company.id}`} className="rounded border px-4 py-2">Annuler</a>
                        <button type="submit" disabled={processing} className="rounded bg-brand-navy px-4 py-2 text-white hover:bg-brand-navy-dark disabled:opacity-50">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </ErpLayout>
    );
}
