import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import {
    BookOpen, Wallet, Landmark, Users, BarChart3, FileText, ShieldCheck,
    ArrowRight, CheckCircle2, Sparkles, TrendingUp, Lock, Zap,
    Building2, Star, Calculator,
} from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<PageProps>().props;

    const modules = [
        { icon: BookOpen, title: 'Comptabilité SYSCOHADA', desc: 'Plan comptable OHADA, journaux, écritures, balance, bilan et compte de résultat en temps réel.', color: 'text-[#1a3a6a]', bg: 'bg-blue-50' },
        { icon: Wallet, title: 'Paie & RH', desc: 'Bulletins de paie professionnels, CNPS, impôts, contrats et calculs automatiques.', color: 'text-[#2d6a4f]', bg: 'bg-green-50' },
        { icon: Landmark, title: 'Fiscalité ivoirienne', desc: 'TVA 18%, déclarations, échéanciers fiscaux et conformité totale avec la DGI.', color: 'text-[#b8860b]', bg: 'bg-amber-50' },
        { icon: BarChart3, title: 'Rapports financiers', desc: 'Balance générale, bilan, compte de résultat et tableaux de bord instantanés.', color: 'text-purple-600', bg: 'bg-purple-50' },
        { icon: Users, title: 'Multi-entreprises', desc: 'Gérez plusieurs sociétés avec un seul compte, basculez en un clic, données isolées.', color: 'text-indigo-600', bg: 'bg-indigo-50' },
        { icon: FileText, title: 'Facturation & Achats', desc: 'Factures clients et fournisseurs, suivi des paiements, TVA et numérotation automatique.', color: 'text-rose-600', bg: 'bg-rose-50' },
    ];

    const stats = [
        { value: '12+', label: 'Modules intégrés' },
        { value: '100%', label: 'Conforme OHADA & DGI' },
        { value: '24/7', label: 'Accessible partout' },
        { value: '1 clic', label: 'Bulletin de paie PDF' },
    ];

    const steps = [
        { icon: Building2, num: '01', title: 'Votre entreprise est configurée', desc: 'Notre équipe active votre espace avec le plan comptable SYSCOHADA, la TVA et les cotisations CNPS déjà en place.' },
        { icon: Users, num: '02', title: 'Vous recevez vos identifiants', desc: 'Un email avec un mot de passe temporaire, à changer dès votre première connexion.' },
        { icon: TrendingUp, num: '03', title: 'Vous pilotez votre activité', desc: 'Compta, paie, taxes, rapports : une seule interface, zéro tableur.' },
    ];

    const avantages = [
        'Bulletins de paie conformes CNPS avec en-tête de votre société',
        'Comptabilité SYSCOHADA : balance, bilan, compte de résultat automatiques',
        'Déclarations TVA et échéances fiscales sans oubli',
        'Multi-entreprises avec isolation totale des données',
        'Imports CSV : employés, écritures comptables en quelques secondes',
        'Tableaux de bord en temps réel pour décider vite et bien',
    ];

    const temoignages = [
        { name: 'A. Kouassi', role: 'DG, société de négoce — Abidjan', text: 'En une semaine, nous avons remplacé 4 tableurs Excel. Les bulletins de paie sortent en 2 clics, propres et professionnels.' },
        { name: 'M. Diabaté', role: 'Comptable agréé — Yamoussoukro', text: 'La balance et le bilan SYSCOHADA générés automatiquement me font gagner 3 jours par mois. Indispensable.' },
        { name: 'F. Traoré', role: 'RH, PME industrielle — San Pedro', text: 'CNPS, impôts, contrats : tout est calculé correctement. Mes employés reçoivent enfin de vrais bulletins.' },
    ];

    return (
        <div className="min-h-screen bg-[#f7f8fb] text-gray-900">
            <Head title="FIDUCIA AFRIC — ERP Comptabilité, Paie & Fiscalité ivoirienne" />

            {/* ═══════════ HEADER STICKY ═══════════ */}
            <header className="sticky top-0 z-40 bg-[#1a3a6a]/95 backdrop-blur border-b border-white/10 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <img src="/images/logo-white.png" alt="FIDUCIA AFRIC" className="h-10 w-auto" onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }} />
                        <div className="leading-tight">
                            <div className="text-lg font-extrabold tracking-wide">
                                <span className="text-white">FIDUCIA</span> <span className="text-[#e6b422]">AFRIC</span>
                            </div>
                            <div className="text-[10px] text-blue-100/70 tracking-[0.2em] uppercase">Conseils & Finances</div>
                        </div>
                    </div>

                    <nav className="hidden md:flex items-center gap-6 text-sm font-medium text-blue-100/80">
                        <a href="#modules" className="hover:text-white">Modules</a>
                        <a href="#avantages" className="hover:text-white">Avantages</a>
                        <a href="#temoignages" className="hover:text-white">Témoignages</a>
                    </nav>

                    <div className="flex items-center gap-2">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-[#1a3a6a] text-sm font-semibold hover:bg-blue-50 shadow">
                                Tableau de bord <ArrowRight className="h-4 w-4" />
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#b8860b] text-white text-sm font-semibold hover:bg-[#96690a] shadow">
                                    Connexion <ArrowRight className="h-4 w-4" />
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            {/* ═══════════ HERO ═══════════ */}
            <section className="relative overflow-hidden bg-gradient-to-br from-[#1a3a6a] via-[#16305a] to-[#0f2242] text-white">
                <div className="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-[#b8860b]/20 blur-3xl"></div>
                <div className="absolute -bottom-32 -left-24 w-96 h-96 rounded-full bg-[#2d6a4f]/20 blur-3xl"></div>

                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div className="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-1.5 text-xs font-semibold tracking-wide">
                            <Sparkles className="h-3.5 w-3.5 text-[#e6b422]" />
                            🇨🇮 Conçu pour les entreprises ivoiriennes & africaines
                        </div>

                        <h1 className="mt-6 text-4xl lg:text-5xl font-extrabold leading-tight">
                            Pilotez votre entreprise avec <span className="text-[#e6b422]">confiance</span> et <span className="text-[#7fc49b]">précision</span>
                        </h1>

                        <p className="mt-5 text-lg text-blue-100/90 leading-relaxed">
                            Le système de gestion intégré de la <strong>comptabilité SYSCOHADA</strong>, de la <strong>paie CNPS</strong> et de la <strong>fiscalité ivoirienne</strong>.
                            Fini les tableurs : tout votre business, dans une seule plateforme sécurisée.
                        </p>

                        <div className="mt-8 flex flex-wrap gap-3">
                            {auth.user ? (
                                <Link href={route('dashboard')} className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#b8860b] text-white font-bold hover:bg-[#96690a] shadow-lg">
                                    Accéder à mon espace <ArrowRight className="h-5 w-5" />
                                </Link>
                            ) : (
                                <>
                                    <Link href={route('login')} className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#b8860b] text-white font-bold hover:bg-[#96690a] shadow-lg">
                                        Se connecter <ArrowRight className="h-5 w-5" />
                                    </Link>
                                    <a href="mailto:contact@fiducia-africa.com" className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white/10 border border-white/25 font-semibold hover:bg-white/20">
                                        Demander un accès
                                    </a>
                                </>
                            )}
                        </div>

                        <div className="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-blue-100/80">
                            <span className="inline-flex items-center gap-1.5"><Calculator className="h-4 w-4 text-[#e6b422]" /> TVA 18% intégrée</span>
                            <span className="inline-flex items-center gap-1.5"><ShieldCheck className="h-4 w-4 text-[#7fc49b]" /> CNPS automatique</span>
                            <span className="inline-flex items-center gap-1.5"><CheckCircle2 className="h-4 w-4 text-[#e6b422]" /> Normes OHADA</span>
                        </div>
                    </div>

                    <div className="hidden lg:block">
                        <div className="max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-8">
                            <img
                                src="/images/branding.png"
                                alt="FIDUCIA AFRIC — Système de gestion intégré"
                                className="w-full h-auto"
                                onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }}
                            />
                        </div>
                    </div>
                </div>

                {/* Barre de stats */}
                <div className="relative border-t border-white/10 bg-white/5">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                        {stats.map((s) => (
                            <div key={s.label}>
                                <div className="text-2xl lg:text-3xl font-extrabold text-[#e6b422]">{s.value}</div>
                                <div className="text-xs lg:text-sm text-blue-100/80 mt-1">{s.label}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* ═══════════ MODULES ═══════════ */}
            <section id="modules" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <div className="text-center max-w-2xl mx-auto">
                    <div className="text-xs font-bold tracking-[0.25em] uppercase text-[#b8860b]">Une plateforme complète</div>
                    <h2 className="mt-3 text-3xl lg:text-4xl font-extrabold text-[#1a3a6a]">Tout ce qu'il faut pour gérer votre entreprise</h2>
                    <p className="mt-4 text-gray-600">Chaque module est pensé pour le contexte ivoirien : devise XOF, CNPS, DGI, plan comptable SYSCOHADA.</p>
                </div>

                <div className="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {modules.map((m) => {
                        const Icon = m.icon;
                        return (
                            <div key={m.title} className="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition p-6">
                                <div className={`inline-flex p-3 rounded-xl ${m.bg}`}>
                                    <Icon className={`h-6 w-6 ${m.color}`} />
                                </div>
                                <h3 className="mt-4 text-lg font-bold text-gray-900 group-hover:text-[#1a3a6a]">{m.title}</h3>
                                <p className="mt-2 text-sm text-gray-600 leading-relaxed">{m.desc}</p>
                            </div>
                        );
                    })}
                </div>
            </section>

            {/* ═══════════ COMMENT ÇA MARCHE ═══════════ */}
            <section className="bg-white border-y border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div className="text-center">
                        <div className="text-xs font-bold tracking-[0.25em] uppercase text-[#2d6a4f]">Simple et rapide</div>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#1a3a6a]">Opérationnel en 3 étapes</h2>
                    </div>
                    <div className="mt-12 grid md:grid-cols-3 gap-8">
                        {steps.map((s) => {
                            const Icon = s.icon;
                            return (
                                <div key={s.num} className="relative bg-[#f7f8fb] rounded-2xl p-6 border border-gray-100">
                                    <div className="absolute -top-4 left-6 bg-[#1a3a6a] text-white text-xs font-extrabold px-3 py-1.5 rounded-full">{s.num}</div>
                                    <Icon className="h-8 w-8 text-[#b8860b]" />
                                    <h3 className="mt-4 font-bold text-gray-900">{s.title}</h3>
                                    <p className="mt-2 text-sm text-gray-600">{s.desc}</p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* ═══════════ AVANTAGES + SÉCURITÉ ═══════════ */}
            <section id="avantages" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div className="text-xs font-bold tracking-[0.25em] uppercase text-[#b8860b]">Pourquoi FIDUCIA AFRIC ?</div>
                    <h2 className="mt-3 text-3xl lg:text-4xl font-extrabold text-[#1a3a6a]">Votre performance, notre engagement</h2>
                    <ul className="mt-8 space-y-4">
                        {avantages.map((a) => (
                            <li key={a} className="flex items-start gap-3">
                                <CheckCircle2 className="h-5 w-5 text-[#2d6a4f] shrink-0 mt-0.5" />
                                <span className="text-gray-700">{a}</span>
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="bg-gradient-to-br from-[#1a3a6a] to-[#0f2242] rounded-3xl p-8 lg:p-10 text-white shadow-2xl">
                    <ShieldCheck className="h-10 w-10 text-[#e6b422]" />
                    <h3 className="mt-4 text-2xl font-extrabold">Sécurité de niveau bancaire</h3>
                    <p className="mt-3 text-blue-100/90 text-sm leading-relaxed">
                        Vos données financières sont chiffrées, sauvegardées et isolées par entreprise. Chaque utilisateur n'accède qu'aux sociétés auxquelles il est autorisé.
                    </p>
                    <div className="mt-8 grid grid-cols-3 gap-4 text-center">
                        <div className="bg-white/10 rounded-xl p-4">
                            <Lock className="h-5 w-5 mx-auto text-[#e6b422]" />
                            <div className="mt-2 text-xs font-semibold">Accès contrôlés</div>
                        </div>
                        <div className="bg-white/10 rounded-xl p-4">
                            <Zap className="h-5 w-5 mx-auto text-[#e6b422]" />
                            <div className="mt-2 text-xs font-semibold">Ultra rapide</div>
                        </div>
                        <div className="bg-white/10 rounded-xl p-4">
                            <ShieldCheck className="h-5 w-5 mx-auto text-[#e6b422]" />
                            <div className="mt-2 text-xs font-semibold">Données isolées</div>
                        </div>
                    </div>
                </div>
            </section>

            {/* ═══════════ TÉMOIGNAGES ═══════════ */}
            <section id="temoignages" className="bg-white border-y border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div className="text-center">
                        <div className="text-xs font-bold tracking-[0.25em] uppercase text-[#2d6a4f]">Ils nous font confiance</div>
                        <h2 className="mt-3 text-3xl font-extrabold text-[#1a3a6a]">Des dirigeants sereins</h2>
                    </div>
                    <div className="mt-12 grid md:grid-cols-3 gap-6">
                        {temoignages.map((t) => (
                            <div key={t.name} className="bg-[#f7f8fb] rounded-2xl p-6 border border-gray-100">
                                <div className="flex gap-1">
                                    {[1, 2, 3, 4, 5].map((i) => <Star key={i} className="h-4 w-4 fill-[#e6b422] text-[#e6b422]" />)}
                                </div>
                                <p className="mt-4 text-sm text-gray-700 leading-relaxed">« {t.text} »</p>
                                <div className="mt-5 flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-full bg-[#1a3a6a] text-white flex items-center justify-center font-bold text-sm">
                                        {t.name.charAt(0)}
                                    </div>
                                    <div>
                                        <div className="text-sm font-bold text-gray-900">{t.name}</div>
                                        <div className="text-xs text-gray-500">{t.role}</div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* ═══════════ CTA FINAL ═══════════ */}
            <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#1a3a6a] to-[#b8860b] p-10 lg:p-14 text-white text-center shadow-2xl">
                    <h2 className="text-3xl lg:text-4xl font-extrabold">Prêt à moderniser votre gestion ?</h2>
                    <p className="mt-4 text-blue-100 max-w-2xl mx-auto">
                        Rejoignez les entreprises qui ont dit adieu aux tableurs et aux erreurs de calcul. Contactez-nous : nous configurons votre espace et vous recevez vos identifiants par email.
                    </p>
                    <div className="mt-8 flex justify-center gap-3 flex-wrap">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-[#1a3a6a] font-extrabold hover:bg-blue-50 shadow-lg">
                                Ouvrir mon tableau de bord <ArrowRight className="h-5 w-5" />
                            </Link>
                        ) : (
                            <>
                                <a href="mailto:contact@fiducia-africa.com" className="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-[#1a3a6a] font-extrabold hover:bg-blue-50 shadow-lg">
                                    Demander un accès <ArrowRight className="h-5 w-5" />
                                </a>
                                <Link href={route('login')} className="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl border-2 border-white/60 font-bold hover:bg-white/10">
                                    Se connecter
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </section>

            {/* ═══════════ FOOTER ═══════════ */}
            <footer className="bg-[#0f2242] text-blue-100/80">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid md:grid-cols-3 gap-8 text-sm">
                    <div>
                        <div className="text-lg font-extrabold text-white">
                            FIDUCIA <span className="text-[#e6b422]">AFRIC</span>
                        </div>
                        <p className="mt-3 leading-relaxed">
                            Système de gestion intégré de la comptabilité, de la paie et de la fiscalité ivoirienne.
                        </p>
                    </div>
                    <div>
                        <div className="font-bold text-white mb-3">Modules</div>
                        <ul className="space-y-2">
                            <li>Comptabilité SYSCOHADA</li>
                            <li>Paie & Ressources Humaines</li>
                            <li>Fiscalité & Déclarations</li>
                            <li>Rapports & Tableaux de bord</li>
                        </ul>
                    </div>
                    <div>
                        <div className="font-bold text-white mb-3">Contact</div>
                        <ul className="space-y-2">
                            <li>Abidjan, Côte d'Ivoire</li>
                            <li>contact@fiducia-africa.com</li>
                            <li>+225 07 00 00 00 00</li>
                        </ul>
                    </div>
                </div>
                <div className="border-t border-white/10 py-5 text-center text-xs tracking-[0.2em] uppercase">
                    — Votre performance, notre engagement —
                </div>
            </footer>
        </div>
    );
}
