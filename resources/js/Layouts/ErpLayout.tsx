import { useState, useEffect, PropsWithChildren } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import Dropdown from '@/Components/Dropdown';
import SystemFeedback from '@/Components/SystemFeedback';
import { PageProps } from '@/types';
import {
    LayoutDashboard, BookOpen, Users, Wallet, Landmark, BarChart3, User, Building2,
    LogOut, Menu, X, ChevronDown, ShoppingCart, ShoppingBag, Package, Banknote, Coins,
    Bell, Upload, Search, Check, Settings, FileText
} from 'lucide-react';

interface Company { id: number; name: string; role: string; }
type ErpPageProps = PageProps & {
    currentCompany?: { id: number; name: string } | null;
};

export default function ErpLayout({ children }: PropsWithChildren) {
    const { auth, currentCompany } = usePage<ErpPageProps>().props;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [companies, setCompanies] = useState<Company[]>([]);
    const [notifCount, setNotifCount] = useState(0);
    const avatar = (auth.user as any).avatar_url as string | undefined;

    useEffect(() => {
        fetch(route('companies.index'), { headers: { Accept: 'application/json' } })
            .then(r => r.json()).then(setCompanies).catch(() => {});
        fetch(route('notifications.index'), { headers: { Accept: 'application/json' } })
            .then(r => r.json()).then(d => setNotifCount(d?.high_count || 0)).catch(() => {});
    }, []);

    const menuItems = [
        { name: 'Tableau de bord', href: route('dashboard'), icon: LayoutDashboard },
        { name: 'Comptabilité', href: route('accounting.index'), icon: BookOpen },
        { name: 'Ressources Humaines', href: route('hr.index'), icon: Users },
        { name: 'Paie', href: route('payroll.index'), icon: Wallet },
        { name: 'Fiscalité', href: route('tax.index'), icon: Landmark },
        { name: 'Rapports', href: route('reporting.index'), icon: BarChart3 },
        { name: 'Achats', href: route('purchasing.index'), icon: ShoppingCart },
        { name: 'Ventes', href: route('sales.index'), icon: ShoppingBag },
        { name: 'Immobilisations', href: route('assets.index'), icon: Package },
        { name: 'Trésorerie', href: route('treasury.index'), icon: Banknote },
        { name: 'Documents', href: route('documents.index'), icon: FileText },
        { name: 'Paramétrage', href: route('settings.index'), icon: Settings },
    ];

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
            <SystemFeedback />
            {/* Sidebar */}
            <aside className="hidden md:flex md:flex-col w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
                <div className="flex items-center justify-center h-24 px-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <img src="/images/logo.png" alt="FIDUCIA AFRIC" className="h-20 w-auto object-contain" />
                </div>
                <nav className="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    {menuItems.map((item) => {
                        const itemPath = new URL(item.href, window.location.origin).pathname;
                        const isActive = itemPath === window.location.pathname;
                        const Icon = item.icon;
                        return (
                            <Link key={item.name} href={item.href} prefetch="intent"
                                className={`flex items-center px-5 py-3 text-base font-medium rounded-lg transition ${
                                    isActive
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 border-l-4 border-indigo-600'
                                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'
                                }`}>
                                <Icon className="mr-4 h-6 w-6" />
                                {item.name}
                            </Link>
                        );
                    })}
                </nav>
                <div className="p-4 border-t border-gray-200 dark:border-gray-700">
                    <div className="text-sm text-gray-500 dark:text-gray-400 mb-2">Entreprise active</div>
                    <div className="text-base font-semibold text-gray-800 dark:text-gray-100 truncate flex items-center">
                        <Building2 className="mr-3 h-5 w-5 text-indigo-500" />
                        {currentCompany?.name || 'FIDUCIA AFRICA'}
                    </div>
                </div>
            </aside>

            {/* Main */}
            <div className="flex-1 flex flex-col min-w-0">
                <header className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex items-center md:hidden">
                                <button onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
                                    className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {showingNavigationDropdown ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                                </button>
                            </div>

                            <div className="flex-1 flex items-center justify-end gap-1">
                                {/* Recherche */}
                                <Link href={route('search')} prefetch="intent" title="Recherche globale (Ctrl+K)"
                                    className="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700">
                                    <Search className="h-5 w-5" />
                                </Link>
                                {/* Notifications + badge */}
                                <Link href={route('notifications.index')} prefetch="intent" title="Notifications"
                                    className="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 relative">
                                    <Bell className="h-5 w-5" />
                                    {notifCount > 0 && (
                                        <span className="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                            {notifCount > 9 ? '9+' : notifCount}
                                        </span>
                                    )}
                                </Link>

                                {/* ===== SELECTEUR ENTREPRISE HEADER ===== */}
                                {companies.length > 0 && (
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button
                                                type="button"
                                                title={currentCompany ? "Entreprise active : " + currentCompany.name : "Changer d'entreprise"}
                                                className="flex items-center gap-1 px-2 py-1.5 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700"
                                            >
                                                <Building2 className="h-5 w-5" />
                                                <span className="hidden lg:inline text-sm max-w-[160px] truncate">{currentCompany ? currentCompany.name : ''}</span>
                                                <ChevronDown className="h-3 w-3" />
                                            </button>
                                        </Dropdown.Trigger>
                                        <Dropdown.Content>
                                            <div className="px-3 py-2">
                                                <div className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Changer d'entreprise</div>
                                                <div className="space-y-1 max-h-48 overflow-y-auto">
                                                    {companies.map((c) => (
                                                        <button
                                                            key={c.id}
                                                            type="button"
                                                            onClick={() => router.post(route('companies.switch', c.id))}
                                                            className={'w-full text-left px-3 py-2 rounded-md text-sm flex items-center justify-between ' + (currentCompany?.id === c.id ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700')}
                                                        >
                                                            <span className="truncate">{c.name}</span>
                                                            {currentCompany?.id === c.id && <Check className="h-4 w-4 ml-2 shrink-0" />}
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        </Dropdown.Content>
                                    </Dropdown>
                                )}                                {/* Dropdown utilisateur */}
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button type="button" className="inline-flex items-center px-2 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition">
                                                {avatar ? (
                                                    <img src={avatar} alt={auth.user.name} className="h-8 w-8 rounded-full mr-2 object-cover" />
                                                ) : (
                                                    <div className="h-8 w-8 rounded-full mr-2 bg-indigo-600 text-white flex items-center justify-center text-sm font-bold">
                                                        {auth.user.name.charAt(0).toUpperCase()}
                                                    </div>
                                                )}
                                                <span className="hidden sm:inline">{auth.user.name}</span>
                                                <ChevronDown className="ml-2 h-4 w-4" />
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <div className="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                                            <div className="text-sm font-medium text-gray-900 dark:text-gray-100">{auth.user.name}</div>
                                            <div className="text-xs text-gray-500 dark:text-gray-400">{auth.user.email}</div>
                                        </div>

                                        <Dropdown.Link href={route('profile.edit')}>
                                            <div className="flex items-center"><User className="mr-2 h-4 w-4" /> Profil</div>
                                        </Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            <div className="flex items-center text-red-600 dark:text-red-400"><LogOut className="mr-2 h-4 w-4" /> Déconnexion</div>
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    {/* Mobile nav */}
                    {showingNavigationDropdown && (
                        <div className="md:hidden border-t border-gray-200 dark:border-gray-700">
                            <nav className="px-4 py-4 space-y-2">
                                {menuItems.map((item) => {
                                    const Icon = item.icon;
                                    const isActive = item.href === window.location.pathname;
                                    return (
                                        <Link key={item.name} href={item.href} prefetch="intent"
                                            className={`flex items-center px-5 py-3 text-base font-medium rounded-lg ${
                                                isActive
                                                ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                                                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            }`}>
                                            <Icon className="mr-4 h-6 w-6" />
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </nav>
                        </div>
                    )}
                </header>

                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">{children}</main>
            </div>
        </div>
    );
}
