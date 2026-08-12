import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { PageProps } from '@/types';

export default function Authenticated({ header, children }: { header?: React.ReactNode; children: React.ReactNode }) {
    const user = usePage<PageProps>().props.auth.user;
    const companies = (usePage().props as any).companies || [];
    const activeCompanyId = (usePage().props as any).activeCompanyId;
    const [showingSidebar, setShowingSidebar] = useState(false);

    const navLinks = [
        { href: '/dashboard', label: 'Dashboard', icon: '🏠', pattern: 'dashboard' },
        { href: '/accounting', label: 'Comptabilité', icon: '📒', pattern: 'accounting.*' },
        { href: '/hr/employees', label: 'RH', icon: '👥', pattern: 'hr.*' },
        { href: '/payroll', label: 'Paie', icon: '💰', pattern: 'payroll.*' },
        { href: '/reporting', label: 'Reporting', icon: '📈', pattern: 'reporting.*' },
        { href: '/parametrage', label: 'Paramètres', icon: '⚙️', pattern: 'settings.*' },
    ];

    const closeSidebar = () => setShowingSidebar(false);
    const switchCompany = (id: number) => router.post('/company/switch', { company_id: id });

    return (
        <div className="min-h-screen bg-gray-100">
            <div className="flex h-14 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden">
                <Link href="/"><ApplicationLogo className="h-8 w-auto" /></Link>
                <button onClick={() => setShowingSidebar((s) => !s)} className="rounded-md p-2 text-gray-500 hover:bg-gray-100">
                    <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

            {showingSidebar && <div className="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" onClick={closeSidebar} />}

            <aside className={`fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col border-r border-gray-200 bg-white transition-transform duration-150 ease-in-out lg:translate-x-0 ${showingSidebar ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="flex h-14 items-center gap-2 border-b border-gray-200 px-4">
                    <Link href="/" onClick={closeSidebar} className="flex items-center gap-2">
                        <ApplicationLogo className="h-8 w-auto" />
                        <span className="font-semibold text-gray-800">FIDUCIA ERP</span>
                    </Link>
                </div>

                {/* ═══ SÉLECTEUR D'ENTREPRISE ═══ */}
                {companies.length > 0 && (
                    <div className="border-b border-gray-200 p-3">
                        <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400">Entreprise active</label>
                        <select
                            value={activeCompanyId || ''}
                            onChange={(e) => switchCompany(Number(e.target.value))}
                            className="w-full rounded-md border-gray-300 bg-gray-50 px-2 py-2 text-sm font-medium text-gray-800 focus:border-gray-900"
                        >
                            {companies.map((c: any) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>
                )}

                <nav className="flex-1 overflow-y-auto px-3 py-4">
                    <div className="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Navigation</div>
                    {navLinks.map((link) => {
                        const active = route().current(link.pattern);
                        return (
                            <Link key={link.href} href={link.href} onClick={closeSidebar}
                                className={`mb-1 flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition ${active ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100'}`}>
                                <span className="text-lg">{link.icon}</span>
                                <span>{link.label}</span>
                            </Link>
                        );
                    })}
                </nav>

                <div className="border-t border-gray-200 p-3">
                    <div className="flex items-center gap-3 rounded-md bg-gray-50 p-2">
                        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white">{user.name ? user.name.charAt(0) : '?'}</div>
                        <div className="min-w-0 flex-1">
                            <div className="truncate text-sm font-medium text-gray-900">{user.name}</div>
                            <div className="truncate text-xs text-gray-500">{user.email}</div>
                        </div>
                    </div>
                    <div className="mt-2 flex gap-2">
                        <Link href={route('profile.edit')} className="flex-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50">Profil</Link>
                        <Link method="post" as="button" href={route('logout')} className="flex-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-center text-xs font-medium text-gray-700 hover:bg-gray-50">Déconnexion</Link>
                    </div>
                </div>
            </aside>

            <div className="flex min-h-screen flex-col lg:pl-64">
                {header && <header className="bg-white shadow"><div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div></header>}
                <main className="flex-1">{children}</main>
            </div>
        </div>
    );
}
