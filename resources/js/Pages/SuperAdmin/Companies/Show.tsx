import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

interface Plan { id: number; name: string; slug: string; max_users: number; max_employees: number; price: number; trial_days: number; }
interface Subscription { id: number; plan_id: number; status: string; starts_at: string; ends_at: string | null; trial_ends_at: string | null; plan: Plan; }
interface CompanyUser { id: number; name: string; email: string; pivot: { role: string; is_active: boolean }; }
interface CompanyDetail {
    id: number; name: string; short_name: string | null; slug: string; email: string | null; phone: string | null;
    address: string | null; rccm: string | null; tax_id: string | null; currency: string; timezone: string;
    is_active: boolean; suspended_at: string | null; users: CompanyUser[];
}
interface Stats { employees: number; payslips: number; clients: number; invoices: number; }
const formatMoney = (v: number) => (v || 0).toLocaleString('fr-FR') + ' FCFA';
const statusLabel: Record<string, string> = { trial: 'Essai', active: 'Actif', cancelled: 'Résilié', expired: 'Expiré' };
const statusColor: Record<string, string> = {
    trial: 'bg-blue-100 text-blue-800', active: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-600', expired: 'bg-red-100 text-red-800',
};

export default function Show({ company, stats, subscription, plans }: { company: CompanyDetail; stats: Stats; subscription: Subscription | null; plans: Plan[] }) {
    const flash = (usePage().props as any).flash;

    const toggleActive = () => {
        if (confirm(company.is_active ? `Suspendre "${company.name}" ? Tous ses utilisateurs seront immédiatement déconnectés.` : `Réactiver "${company.name}" ?`)) {
            router.post(`/super-admin/companies/${company.id}/toggle-active`);
        }
    };

    return (
        <ErpLayout>
            <Head title={`${company.name} — Super Admin`} />
            <div className="py-8 px-6 max-w-5xl mx-auto">
                <div className="mb-6">
                    <Link href="/super-admin/companies" className="text-sm text-blue-600 hover:underline">← Toutes les entreprises</Link>
                </div>

                <div className="mb-6 flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-3">
                            🏢 {company.name}
                            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${company.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                {company.is_active ? 'Active' : 'Suspendue'}
                            </span>
                        </h1>
                        <p className="text-sm text-gray-500 mt-1">{company.short_name || company.slug}</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={`/super-admin/companies/${company.id}/edit`} className="rounded border border-gray-300 bg-white px-4 py-2 text-sm hover:bg-gray-50">
                            ✏️ Éditer
                        </Link>
                        <button onClick={toggleActive} className={`rounded px-4 py-2 text-sm text-white ${company.is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700'}`}>
                            {company.is_active ? 'Suspendre' : 'Réactiver'}
                        </button>
                    </div>
                </div>

                {flash?.success && <div className="mb-6 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
                {flash?.error && <div className="mb-6 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}

                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <StatCard label="Employés" value={stats.employees} />
                    <StatCard label="Bulletins de paie" value={stats.payslips} />
                    <StatCard label="Clients" value={stats.clients} />
                    <StatCard label="Factures" value={stats.invoices} />
                </div>

                <div className="grid md:grid-cols-2 gap-6 mb-6">
                    <div className="bg-white rounded-lg shadow-sm border p-5">
                        <h2 className="font-bold text-gray-900 mb-4">Informations</h2>
                        <dl className="space-y-2 text-sm">
                            <Info label="Email" value={company.email} />
                            <Info label="Téléphone" value={company.phone} />
                            <Info label="Adresse" value={company.address} />
                            <Info label="RCCM" value={company.rccm} />
                            <Info label="N° fiscal" value={company.tax_id} />
                            <Info label="Devise" value={company.currency} />
                            <Info label="Fuseau horaire" value={company.timezone} />
                            {company.suspended_at && <Info label="Suspendue depuis" value={new Date(company.suspended_at).toLocaleDateString('fr-FR')} />}
                        </dl>
                    </div>

                    <SubscriptionCard company={company} subscription={subscription} plans={plans} />
                </div>

                <div className="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div className="px-5 py-4 border-b"><h2 className="font-bold text-gray-900">Utilisateurs ({company.users.length})</h2></div>
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50"><tr className="text-left text-gray-600">
                            <th className="px-5 py-3">Nom</th><th className="px-5 py-3">Email</th>
                            <th className="px-5 py-3">Rôle</th><th className="px-5 py-3">Statut</th>
                        </tr></thead>
                        <tbody>
                            {company.users.length === 0 ? (
                                <tr><td colSpan={4} className="px-5 py-8 text-center text-gray-500">Aucun utilisateur.</td></tr>
                            ) : company.users.map((u) => (
                                <tr key={u.id} className="border-t hover:bg-gray-50">
                                    <td className="px-5 py-3 font-medium">{u.name}</td>
                                    <td className="px-5 py-3 text-gray-600">{u.email}</td>
                                    <td className="px-5 py-3">{u.pivot.role}</td>
                                    <td className="px-5 py-3">
                                        <span className={`rounded px-2 py-0.5 text-xs ${u.pivot.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}`}>
                                            {u.pivot.is_active ? 'Actif' : 'Inactif'}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </ErpLayout>
    );
}

function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <div className="bg-white rounded-lg shadow-sm border p-4">
            <p className="text-xs text-gray-500 uppercase">{label}</p>
            <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
    );
}

function Info({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-gray-500">{label}</dt>
            <dd className="text-gray-900 text-right">{value || '—'}</dd>
        </div>
    );
}

function SubscriptionCard({ company, subscription, plans }: { company: CompanyDetail; subscription: Subscription | null; plans: Plan[] }) {
    const { data, setData, post, processing, errors } = useForm({
        plan_id: subscription?.plan_id ?? plans[0]?.id ?? '',
        status: subscription?.status ?? 'trial',
        starts_at: new Date().toISOString().slice(0, 10),
        ends_at: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/super-admin/companies/${company.id}/subscription`);
    };

    return (
        <div className="bg-white rounded-lg shadow-sm border p-5">
            <h2 className="font-bold text-gray-900 mb-4">Abonnement</h2>

            {subscription ? (
                <div className="mb-4 p-3 rounded bg-gray-50 border text-sm space-y-1">
                    <div className="flex justify-between">
                        <span className="font-semibold">{subscription.plan.name}</span>
                        <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${statusColor[subscription.status] || 'bg-gray-100 text-gray-600'}`}>
                            {statusLabel[subscription.status] || subscription.status}
                        </span>
                    </div>
                    <div className="text-gray-500">{formatMoney(subscription.plan.price)} / mois</div>
                    <div className="text-gray-500">Depuis le {new Date(subscription.starts_at).toLocaleDateString('fr-FR')}</div>
                    {subscription.ends_at && <div className="text-gray-500">Jusqu'au {new Date(subscription.ends_at).toLocaleDateString('fr-FR')}</div>}
                </div>
            ) : (
                <p className="mb-4 text-sm text-gray-500">Aucun abonnement actif pour cette entreprise.</p>
            )}

            <form onSubmit={submit} className="space-y-3 border-t pt-4">
                <p className="text-xs font-semibold text-gray-500 uppercase">Attribuer / modifier le plan</p>
                <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Plan</label>
                    <select value={data.plan_id} onChange={(e) => setData('plan_id', +e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                        {plans.map((p) => <option key={p.id} value={p.id}>{p.name} — {formatMoney(p.price)}/mois</option>)}
                    </select>
                    {errors.plan_id && <p className="text-xs text-red-600 mt-1">{errors.plan_id}</p>}
                </div>
                <div className="grid grid-cols-2 gap-3">
                    <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Statut</label>
                        <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
                            <option value="trial">Essai</option>
                            <option value="active">Actif</option>
                            <option value="cancelled">Résilié</option>
                            <option value="expired">Expiré</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-gray-700 mb-1">Début</label>
                        <input type="date" value={data.starts_at} onChange={(e) => setData('starts_at', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required />
                    </div>
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Fin (optionnel)</label>
                    <input type="date" value={data.ends_at} onChange={(e) => setData('ends_at', e.target.value)} className="w-full rounded-md border-gray-300 text-sm" />
                    {errors.ends_at && <p className="text-xs text-red-600 mt-1">{errors.ends_at}</p>}
                </div>
                <button type="submit" disabled={processing} className="w-full rounded-md bg-brand-navy text-white text-sm py-2 hover:bg-brand-navy-dark disabled:opacity-50">
                    {processing ? 'Enregistrement...' : 'Valider l\'abonnement'}
                </button>
            </form>
        </div>
    );
}
