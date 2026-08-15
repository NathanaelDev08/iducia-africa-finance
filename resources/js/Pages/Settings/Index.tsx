import SettingsCrud from './SettingsCrud';
import { useState } from 'react'; // Add this import
import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

export default function SettingsIndex(props: any) {
    const {
        company, tab, menu,
        users = [], modules = [], taxes = [], contributions = [], pay_items = [],
        journals = [], charts = [], settings = {},
    } = props;

    const changeTab = (t: string) => {
        router.get('/parametrage', { tab: t }, { preserveState: true, preserveScroll: true });
    };

    return (
        <ErpLayout>
            <Head title="Paramètres" />
            <div className="min-h-screen bg-gray-100">
                {/* En-tête */}
                <div className="bg-white border-b border-gray-200">
                    <div className="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <h1 className="text-2xl font-bold text-gray-800">⚙️ Paramètres</h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Configuration de l'entreprise <span className="font-semibold text-gray-900">{company?.name}</span>
                        </p>
                    </div>
                </div>

                <div className="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* ═══ ONGLETS HORIZONTAUX (fusionnés, pas de sidebar) ═══ */}
                    <div className="mb-6 overflow-x-auto bg-white border-b border-gray-200 shadow-sm">
                        <nav className="flex">
                            {(menu || []).map((item: any) => {
                                const className =
                                    'flex items-center gap-2 whitespace-nowrap border-b-2 px-5 py-3 text-sm font-medium transition ' +
                                    (tab === item.key
                                        ? 'border-brand-navy bg-gray-50 text-brand-navy'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700');

                                if (item.href) {
                                    return (
                                        <Link key={item.key} href={item.href} title={item.description} className={className}>
                                            <span className="text-lg">{item.icon}</span>
                                            <span>{item.label}</span>
                                        </Link>
                                    );
                                }

                                return (
                                    <button
                                        key={item.key}
                                        type="button"
                                        onClick={() => changeTab(item.key)}
                                        title={item.description}
                                        className={className}
                                    >
                                        <span className="text-lg">{item.icon}</span>
                                        <span>{item.label}</span>
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    {/* Contenu de l'onglet actif */}
                    {tab === 'company' && <CompanySection company={company} />}
                    {tab === 'users' && <UsersSection users={users} />}
                    {tab === 'taxes' && <TaxesSection taxes={taxes} />}
                    {tab === 'payroll' && <PayrollSection contributions={contributions} payItems={pay_items} />}
                    {tab === 'accounting' && <AccountingSection journals={journals} charts={charts} />}
                    {tab === 'general' && <GeneralSection settings={settings} company={company} />}
                    {['taxes','payroll','accounting','users'].includes(tab) && <SettingsCrud tab={tab} />}
                    {tab === 'user-management' && <UserManagementSection users={users} companies={[company]} modules={modules} />}
                </div>
            </div>
        </ErpLayout>
    );
}

/* ═══════════ SECTIONS ═══════════ */

function CompanySection({ company }: any) {
    const { data, setData, put, processing, errors } = useForm({
        name: company?.name || '',
        short_name: company?.short_name || '',
        email: company?.email || '',
        phone: company?.phone || '',
        address: company?.address || '',
        rccm: company?.rccm || '',
        tax_id: company?.tax_id || '',
    });

    const submit = (e: any) => {
        e.preventDefault();
        put('/parametrage/company/' + company.id);
    };

    return (
        <div className="space-y-6">
            <div className="bg-white rounded-lg shadow">
                <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                    <span className="text-2xl">🏢</span>
                    <div>
                        <h2 className="text-lg font-semibold text-gray-800">Informations de l'entreprise</h2>
                        <p className="text-sm text-gray-500">Identité légale et coordonnées</p>
                    </div>
                </div>
                <form onSubmit={submit} className="p-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div className="md:col-span-2">
                            <label className="block mb-1 text-sm font-medium text-gray-700">Raison sociale *</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block mb-1 text-sm font-medium text-gray-700">Nom abrégé</label>
                            <input type="text" value={data.short_name} onChange={(e) => setData('short_name', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                        <div>
                            <label className="block mb-1 text-sm font-medium text-gray-700">Email</label>
                            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                        <div>
                            <label className="block mb-1 text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" value={data.phone} onChange={(e) => setData('phone', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                        <div>
                            <label className="block mb-1 text-sm font-medium text-gray-700">RCCM</label>
                            <input type="text" value={data.rccm} onChange={(e) => setData('rccm', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                        <div>
                            <label className="block mb-1 text-sm font-medium text-gray-700">N° Identification fiscale</label>
                            <input type="text" value={data.tax_id} onChange={(e) => setData('tax_id', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block mb-1 text-sm font-medium text-gray-700">Adresse complète</label>
                            <textarea value={data.address} onChange={(e) => setData('address', e.target.value)} rows={3}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                        </div>
                    </div>
                    <div className="flex justify-end gap-2 mt-6">
                        <button type="reset" className="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Annuler</button>
                        <button type="submit" disabled={processing}
                            className="px-4 py-2 text-white bg-gray-900 rounded-md hover:bg-gray-800 disabled:opacity-50">
                            💾 Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            <div className="p-6 bg-white rounded-lg shadow">
                <h3 className="mb-3 font-semibold text-gray-800">📊 Informations système</h3>
                <div className="grid grid-cols-2 gap-4 text-sm">
                    <div><span className="text-gray-500">Devise :</span> <strong>{company?.currency}</strong></div>
                    <div><span className="text-gray-500">Fuseau horaire :</span> <strong>{company?.timezone}</strong></div>
                    <div><span className="text-gray-500">Statut :</span> <span className="font-medium text-green-600">Active</span></div>
                </div>
            </div>
        </div>
    );
}

function UsersSection({ users }: any) {
    const roleLabels: any = {
        'admin': 'Administrateur',
        'accountant': 'Comptable',
        'hr-manager': 'Resp. RH',
        'manager': 'Manager',
        'employee': 'Employé',
    };

    return (
        <div className="bg-white rounded-lg shadow">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div className="flex items-center gap-3">
                    <span className="text-2xl">👥</span>
                    <div>
                        <h2 className="text-lg font-semibold text-gray-800">Utilisateurs ({users.length})</h2>
                        <p className="text-sm text-gray-500">Comptes ayant accès à cette entreprise</p>
                    </div>
                </div>
                <span className="px-3 py-1 text-sm text-white bg-gray-900 rounded-full">{users.length} compte(s)</span>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="border-b bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Utilisateur</th>
                            <th className="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Rôle</th>
                            <th className="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Statut</th>
                            <th className="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Dernière activité</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                        {users.map((user: any) => (
                            <tr key={user.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4">
                                    <div className="flex items-center gap-3">
                                        <div className="flex items-center justify-center w-10 h-10 font-semibold text-white bg-gray-900 rounded-full">
                                            {user.name ? user.name.charAt(0) : '?'}
                                        </div>
                                        <div>
                                            <div className="font-medium text-gray-900">{user.name}</div>
                                            <div className="text-sm text-gray-500">{user.email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-6 py-4">
                                    <span className="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800">
                                        {roleLabels[user.pivot?.role] || user.pivot?.role || 'Employé'}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <span className={'rounded-full px-2.5 py-1 text-xs font-medium ' + (user.pivot?.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')}>
                                        {user.pivot?.is_active ? '● Actif' : '● Inactif'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-500">
                                    {user.last_seen_at ? new Date(user.last_seen_at).toLocaleDateString('fr-FR') : 'Jamais connecté'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function TaxesSection({ taxes }: any) {
    const typeLabels: any = { vat: 'TVA', other: 'Autre' };

    return (
        <div className="bg-white rounded-lg shadow">
            <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                <span className="text-2xl">📋</span>
                <div>
                    <h2 className="text-lg font-semibold text-gray-800">Fiscalité ({taxes.length})</h2>
                    <p className="text-sm text-gray-500">Taxes et impôts applicables</p>
                </div>
            </div>
            <div className="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                {taxes.map((tax: any) => (
                    <div key={tax.id} className="p-4 transition border border-gray-200 rounded-lg hover:shadow-md">
                        <div className="flex items-center justify-between mb-2">
                            <span className="font-semibold text-gray-800">{tax.name}</span>
                            <span className="rounded bg-gray-900 px-2 py-0.5 font-mono text-xs text-white">{tax.code}</span>
                        </div>
                        <div className="space-y-1 text-sm text-gray-600">
                            <div>Type : <strong>{typeLabels[tax.type] || tax.type}</strong></div>
                            <div>Statut : <span className={tax.is_active ? 'text-green-600' : 'text-red-600'}>{tax.is_active ? 'Actif' : 'Inactif'}</span></div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function PayrollSection({ contributions, payItems }: any) {
    return (
        <div className="space-y-6">
            <div className="bg-white rounded-lg shadow">
                <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                    <span className="text-2xl">💰</span>
                    <div>
                        <h2 className="text-lg font-semibold text-gray-800">Cotisations sociales</h2>
                        <p className="text-sm text-gray-500">CNPS et autres organismes</p>
                    </div>
                </div>
                <div className="p-6">
                    <table className="w-full">
                        <thead className="border-b bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Cotisation</th>
                                <th className="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Organisme</th>
                                <th className="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200">
                            {contributions.map((c: any) => (
                                <tr key={c.id}>
                                    <td className="px-4 py-3 font-medium text-gray-900">{c.name}</td>
                                    <td className="px-4 py-3 text-sm text-gray-600">{c.organism}</td>
                                    <td className="px-4 py-3">
                                        <span className={'rounded-full px-2 py-0.5 text-xs ' + (c.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')}>
                                            {c.is_active ? 'Actif' : 'Inactif'}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="bg-white rounded-lg shadow">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="font-semibold text-gray-800">Rubriques de paie ({payItems.length})</h3>
                </div>
                <div className="grid grid-cols-1 gap-3 p-6 md:grid-cols-2">
                    {payItems.map((item: any) => (
                        <div key={item.id} className="flex items-center gap-3 p-3 border border-gray-200 rounded">
                            <span className={'h-2 w-2 rounded-full ' + (item.type === 'earning' ? 'bg-green-500' : 'bg-red-500')}></span>
                            <span className="flex-1 text-sm font-medium text-gray-800">{item.name}</span>
                            <span className="font-mono text-xs text-gray-500">{item.code}</span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function AccountingSection({ journals, charts }: any) {
    const typeColors: any = {
        sale: 'bg-green-100 text-green-800',
        sales: 'bg-green-100 text-green-800',
        purchase: 'bg-orange-100 text-orange-800',
        bank: 'bg-blue-100 text-blue-800',
        cash: 'bg-yellow-100 text-yellow-800',
        payroll: 'bg-purple-100 text-purple-800',
        misc: 'bg-gray-100 text-gray-800',
    };

    return (
        <div className="space-y-6">
            <div className="bg-white rounded-lg shadow">
                <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                    <span className="text-2xl">📒</span>
                    <div>
                        <h2 className="text-lg font-semibold text-gray-800">Journaux comptables ({journals.length})</h2>
                        <p className="text-sm text-gray-500">Journaux disponibles pour la saisie</p>
                    </div>
                </div>
                <div className="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                    {journals.map((j: any) => (
                        <div key={j.id} className="p-4 border border-gray-200 rounded-lg">
                            <div className="flex items-center justify-between mb-2">
                                <span className="rounded bg-gray-900 px-2 py-0.5 font-mono text-xs text-white">{j.code}</span>
                                <span className={'rounded-full px-2 py-0.5 text-xs ' + (typeColors[j.type] || 'bg-gray-100')}>{j.type}</span>
                            </div>
                            <div className="font-medium text-gray-800">{j.name}</div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="p-6 bg-white rounded-lg shadow">
                <h3 className="mb-3 font-semibold text-gray-800">📚 Plans comptables</h3>
                <div className="space-y-3">
                    {charts.map((chart: any) => (
                        <div key={chart.id} className="flex items-center justify-between p-4 border border-gray-200 rounded">
                            <div>
                                <div className="font-medium text-gray-900">{chart.name}</div>
                                <div className="text-sm text-gray-500">{chart.standard} - {chart.version}</div>
                            </div>
                            <span className="px-3 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                {chart.is_default ? '★ Par défaut' : 'Actif'}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function GeneralSection({ settings, company }: any) {
    const { data, setData, put, processing } = useForm({
        language: settings?.language?.value || 'fr',
        timezone: settings?.timezone?.value || 'Africa/Abidjan',
        invoice_payment_days: settings?.invoice_payment_days?.value || '30',
    });

    const submit = (e: any) => {
        e.preventDefault();
        put('/parametrage/general/' + company.id);
    };

    return (
        <div className="bg-white rounded-lg shadow">
            <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                <span className="text-2xl">⚙️</span>
                <div>
                    <h2 className="text-lg font-semibold text-gray-800">Préférences système</h2>
                    <p className="text-sm text-gray-500">Paramètres généraux de fonctionnement</p>
                </div>
            </div>
            <form onSubmit={submit} className="p-6 space-y-4">
                <div>
                    <label className="block mb-1 text-sm font-medium text-gray-700">🌍 Langue de l'interface</label>
                    <select value={data.language} onChange={(e) => setData('language', e.target.value)}
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="fr">🇫🇷 Français</option>
                        <option value="en">🇬🇧 English</option>
                    </select>
                </div>
                <div>
                    <label className="block mb-1 text-sm font-medium text-gray-700">🕐 Fuseau horaire</label>
                    <select value={data.timezone} onChange={(e) => setData('timezone', e.target.value)}
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="Africa/Abidjan">Africa/Abidjan (GMT+0)</option>
                        <option value="Africa/Dakar">Africa/Dakar (GMT+0)</option>
                        <option value="Africa/Lagos">Africa/Lagos (GMT+1)</option>
                        <option value="Europe/Paris">Europe/Paris (GMT+1)</option>
                    </select>
                </div>
                <div>
                    <label className="block mb-1 text-sm font-medium text-gray-700">📅 Délai de paiement factures (jours)</label>
                    <input type="number" value={data.invoice_payment_days}
                        onChange={(e) => setData('invoice_payment_days', e.target.value)}
                        min="0" max="90"
                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-900 focus:ring-gray-900" />
                </div>
                <div className="flex justify-end pt-4">
                    <button type="submit" disabled={processing}
                        className="px-4 py-2 text-white bg-gray-900 rounded-md hover:bg-gray-800 disabled:opacity-50">
                        💾 Enregistrer
                    </button>
                </div>
            </form>
        </div>
    );
}

function UserManagementSection({ users, companies, modules }: any) {
    const [showForm, setShowForm] = useState(false);
    const [view, setView] = useState<ViewMode>('list');
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        company_id: companies[0]?.id || '',
        role: 'employee',
        modules: [] as number[],
        module_permissions: [] as any[],
    });

    const toggleModule = (module: any) => {
        const exists = data.module_permissions.some((item: any) => item.module_id === module.id);
        const nextPermissions = exists
            ? data.module_permissions.filter((item: any) => item.module_id !== module.id)
            : [...data.module_permissions, { module_id: module.id, can_view: true, can_create: false, can_edit: false, can_delete: false }];

        setData({
            ...data,
            modules: nextPermissions.map((item: any) => item.module_id),
            module_permissions: nextPermissions,
        });
    };

    const togglePermission = (moduleId: number, permission: string, checked: boolean) => {
        const nextPermissions = data.module_permissions.map((item: any) =>
            item.module_id === moduleId ? { ...item, [permission]: checked } : item
        );
        setData('module_permissions', nextPermissions);
    };

    const submit = (e: any) => {
        e.preventDefault();
        post('/super-admin/users', {
            onSuccess: () => {
                setShowForm(false);
                setData({
                    name: '',
                    email: '',
                    company_id: companies[0]?.id || '',
                    role: 'employee',
                    modules: [],
                    module_permissions: [],
                });
            },
        });
    };

    return (
        <div className="space-y-6">
            <div className="bg-white rounded-lg shadow">
                <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div className="flex items-center gap-3">
                        <span className="text-2xl">🛡️</span>
                        <div>
                            <h2 className="text-lg font-semibold text-gray-800">Gestion des utilisateurs ({users.length})</h2>
                            <p className="text-sm text-gray-500">Créer des comptes et attribuer les modules</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <ViewSwitcher value={view} onChange={setView} />
                        <button type="button" onClick={() => setShowForm(!showForm)} className="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">
                            {showForm ? 'Fermer' : '+ Nouvel utilisateur'}
                        </button>
                    </div>
                </div>

                {showForm && (
                    <form onSubmit={submit} className="p-6 space-y-4 border-b border-gray-200">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block mb-1 text-sm font-medium">Nom *</label>
                                <input value={data.name} onChange={(e) => setData('name', e.target.value)} required className="w-full px-3 py-2 border-gray-300 rounded-md" />
                            </div>
                            <div>
                                <label className="block mb-1 text-sm font-medium">Email *</label>
                                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required className="w-full px-3 py-2 border-gray-300 rounded-md" />
                            </div>
                            <div>
                                <label className="block mb-1 text-sm font-medium">Entreprise *</label>
                                <select value={data.company_id} onChange={(e) => setData('company_id', e.target.value)} className="w-full px-3 py-2 border-gray-300 rounded-md">
                                    {companies.map((c: any) => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block mb-1 text-sm font-medium">Rôle *</label>
                                <select value={data.role} onChange={(e) => setData('role', e.target.value)} className="w-full px-3 py-2 border-gray-300 rounded-md">
                                    <option value="admin">Administrateur</option>
                                    <option value="accountant">Comptable</option>
                                    <option value="hr-manager">Resp. RH</option>
                                    <option value="manager">Manager</option>
                                    <option value="employee">Employé</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label className="block mb-2 text-sm font-medium">🧩 Modules autorisés *</label>
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                {modules.map((m: any) => {
                                    const selected = data.module_permissions.some((item: any) => item.module_id === m.id);
                                    const permissions = data.module_permissions.find((item: any) => item.module_id === m.id) || { can_view: true, can_create: false, can_edit: false, can_delete: false };

                                    return (
                                        <div key={m.id} className={'rounded border p-3 ' + (selected ? 'border-gray-900 bg-gray-50' : 'border-gray-200')}>
                                            <label className="flex cursor-pointer items-center gap-2 text-sm font-medium">
                                                <input type="checkbox" checked={selected} onChange={() => toggleModule(m)} />
                                                <span>{m.icon}</span>
                                                <span>{m.name}</span>
                                            </label>
                                            {selected && (
                                                <div className="mt-3 flex flex-wrap gap-2 text-xs">
                                                    {['can_view','can_create','can_edit','can_delete'].map((perm) => (
                                                        <label key={perm} className="flex items-center gap-1 rounded border bg-white px-2 py-1">
                                                            <input type="checkbox" checked={Boolean(permissions[perm])} onChange={(e) => togglePermission(m.id, perm, e.target.checked)} />
                                                            <span>{perm === 'can_view' ? 'Voir' : perm === 'can_create' ? 'Créer' : perm === 'can_edit' ? 'Modifier' : 'Supprimer'}</span>
                                                        </label>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                            {errors.modules && <p className="mt-1 text-sm text-red-600">{errors.modules}</p>}
                        </div>

                        <div className="p-3 text-sm text-blue-800 border border-blue-200 rounded-md bg-blue-50">
                            📧 L'utilisateur recevra un email avec son mot de passe temporaire et devra le changer à la première connexion.
                        </div>

                        <div className="flex justify-end gap-2">
                            <button type="button" onClick={() => setShowForm(false)} className="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Annuler</button>
                            <button disabled={processing} className="px-4 py-2 text-white bg-gray-900 rounded-md disabled:opacity-50">
                                Créer + envoyer l'email
                            </button>
                        </div>
                    </form>
                )}

                {view === 'list' ? (
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="text-xs text-left text-gray-500 uppercase border-b bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3">Utilisateur</th>
                                    <th className="px-4 py-3">Entreprises</th>
                                    <th className="px-4 py-3">Modules</th>
                                    <th className="px-4 py-3">Statut</th>
                                    <th className="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {users.map((u: any) => (
                                    <tr key={u.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-gray-900">{u.name}</div>
                                            <div className="text-sm text-gray-500">{u.email}</div>
                                        </td>
                                        <td className="px-4 py-3 text-sm">{u.companies?.map((c: any) => c.name).join(', ') || '—'}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                {u.modules?.map((m: any) => (
                                                    <span key={m.id} className="rounded bg-gray-100 px-2 py-0.5 text-xs">
                                                        {m.icon} {m.name}
                                                    </span>
                                                ))}
                                                {(!u.modules || u.modules.length === 0) && <span className="text-xs text-gray-400">Aucun module</span>}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className={'rounded-full px-2 py-0.5 text-xs ' + (u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')}>
                                                {u.is_active ? '● Actif' : '● Inactif'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 space-x-2">
                                            <button type="button" onClick={() => router.post('/super-admin/users/' + u.id + '/reset-password')} className="text-sm text-orange-600 hover:underline">Reset MDP</button>
                                            <button type="button" onClick={() => router.post('/super-admin/users/' + u.id + '/toggle')} className="text-sm text-blue-600 hover:underline">
                                                {u.is_active ? 'Désactiver' : 'Activer'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                {users.length === 0 && (
                                    <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-500">Aucun utilisateur</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="p-4">
                        <KanbanBoard
                            data={users}
                            rowKey={(u: any) => u.id}
                            groupBy={(u: any) => (u.is_active ? 'active' : 'inactive')}
                            columns={[
                                { key: 'active', label: '● Actifs', colorClass: 'bg-green-100 text-green-800' },
                                { key: 'inactive', label: '● Inactifs', colorClass: 'bg-red-100 text-red-800' },
                            ]}
                            emptyMessage="Aucun utilisateur"
                            renderCard={(u: any) => (
                                <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                    <div className="font-medium text-gray-900 dark:text-gray-100">{u.name}</div>
                                    <div className="text-xs text-gray-500 dark:text-gray-400">{u.email}</div>
                                    <div className="mt-2 text-xs text-gray-500 dark:text-gray-400">{u.companies?.map((c: any) => c.name).join(', ') || '—'}</div>
                                    <div className="mt-2 flex flex-wrap gap-1">
                                        {u.modules?.map((m: any) => (
                                            <span key={m.id} className="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">{m.icon} {m.name}</span>
                                        ))}
                                        {(!u.modules || u.modules.length === 0) && <span className="text-xs text-gray-400">Aucun module</span>}
                                    </div>
                                    <div className="mt-3 flex gap-3 border-t border-gray-100 pt-2 text-xs dark:border-gray-700">
                                        <button type="button" onClick={() => router.post('/super-admin/users/' + u.id + '/reset-password')} className="text-orange-600 hover:underline">Reset MDP</button>
                                        <button type="button" onClick={() => router.post('/super-admin/users/' + u.id + '/toggle')} className="text-blue-600 hover:underline">
                                            {u.is_active ? 'Désactiver' : 'Activer'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
