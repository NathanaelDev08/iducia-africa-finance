import { FormEventHandler, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-[#f5f6f8] to-[#e8eaef] flex items-center justify-center p-4">
            <Head title="Connexion — FIDUCIA AFRIC" />

            <div className="w-full max-w-md">
                {/* ═══════════ EN-TÊTE BRANDING ═══════════ */}
                <div className="text-center mb-6">
                    <Link href="/" className="inline-block">
                        <img 
                            src="/images/logo.png" 
                            alt="FIDUCIA AFRIC" 
                            className="h-16 w-auto mx-auto mb-3"
                            onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }}
                        />
                        <div className="text-2xl font-bold tracking-wide">
                            <span className="text-[#1a3a6a]">FIDUCIA</span>{' '}
                            <span className="text-[#b8860b]">AFRIC</span>
                        </div>
                        <div className="text-xs text-gray-500 mt-1 tracking-[0.2em] uppercase">
                            Conseils et Finances
                        </div>
                    </Link>
                </div>

                {/* ═══════════ FORMULAIRE ═══════════ */}
                <div className="bg-white rounded-xl shadow-xl border border-gray-100 p-8">
                    {new URLSearchParams(window.location.search).has("blocked") && (
                        <div className="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                            🚫 L'accès de votre entreprise au système a été suspendu. Contactez votre prestataire.
                        </div>
                    )}
                    <h1 className="text-xl font-bold text-gray-900 mb-1">Connexion</h1>
                    <p className="text-sm text-gray-500 mb-6">
                        Accédez à votre espace FIDUCIA AFRIC
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        {/* Email */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Adresse email
                            </label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="vous@entreprise.com"
                                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1a3a6a] focus:border-transparent text-sm"
                                required
                                autoFocus
                            />
                            {errors.email && (
                                <p className="text-xs text-red-600 mt-1">{errors.email}</p>
                            )}
                        </div>

                        {/* Mot de passe */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Mot de passe
                            </label>
                            <div className="relative">
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    className="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1a3a6a] focus:border-transparent text-sm"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    {showPassword ? '🙈' : '👁️'}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-xs text-red-600 mt-1">{errors.password}</p>
                            )}
                        </div>

                        {/* Se souvenir de moi */}
                        <div className="flex items-center justify-between">
                            <label className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded border-gray-300 text-[#1a3a6a] focus:ring-[#1a3a6a]"
                                />
                                <span className="ml-2 text-sm text-gray-600">Se souvenir de moi</span>
                            </label>
                        </div>

                        {/* Bouton */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-[#1a3a6a] text-white font-semibold py-3 rounded-lg hover:bg-[#142c52] transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                        >
                            {processing ? 'Connexion...' : 'Se connecter'}
                        </button>
                    </form>

                    {/* Lien inscription */}
                    <div className="mt-6 text-center text-sm text-gray-600">
                        Pas encore de compte ?{' '}
                        <Link href={route('register')} className="text-[#1a3a6a] font-semibold hover:underline">
                            Créer un compte
                        </Link>
                    </div>
                </div>

                {/* ═══════════ PIED DE PAGE ═══════════ */}
                <div className="mt-6 text-center text-xs text-gray-500">
                    <p>FIDUCIA AFRIC — Système ERP de gestion</p>
                    <p className="mt-1">Comptabilité • Paie • Fiscalité Ivoirienne</p>
                </div>
            </div>
        </div>
    );
}
