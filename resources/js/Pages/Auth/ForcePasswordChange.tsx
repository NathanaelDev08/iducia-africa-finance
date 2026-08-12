import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function ForcePasswordChange() {
    const { data, setData, put, processing, errors } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const [showPassword, setShowPassword] = useState(false);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/password/change');
    };

    const passwordStrength = (pwd: string) => {
        let score = 0;
        if (pwd.length >= 8) score++;
        if (pwd.length >= 12) score++;
        if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
        if (/\d/.test(pwd)) score++;
        if (/[!@#$%&*?]/.test(pwd)) score++;
        return score;
    };

    const strength = passwordStrength(data.password);
    const strengthLabels = ['', 'Très faible', 'Faible', 'Moyen', 'Fort', 'Excellent'];
    const strengthColors = ['', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-emerald-600'];

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
            <Head title="Changer mon mot de passe" />

            <div className="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden">
                <div className="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white text-center">
                    <div className="text-4xl mb-2">🔐</div>
                    <h1 className="text-xl font-bold">Première connexion</h1>
                    <p className="text-sm opacity-90 mt-1">Définissez votre mot de passe personnel</p>
                </div>

                <form onSubmit={submit} className="p-6 space-y-4">
                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                        <strong>⚠️ Sécurité :</strong> Pour protéger votre compte, vous devez définir un nouveau mot de passe personnel.
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Mot de passe temporaire *</label>
                        <input
                            type={showPassword ? 'text' : 'password'}
                            value={data.current_password}
                            onChange={(e) => setData('current_password', e.target.value)}
                            className="w-full rounded border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        {errors.current_password && <p className="text-xs text-red-600 mt-1">{errors.current_password}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
                        <input
                            type={showPassword ? 'text' : 'password'}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="w-full rounded border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            required
                            minLength={8}
                        />
                        {data.password && (
                            <div className="mt-2">
                                <div className="flex gap-1 mb-1">
                                    {[1, 2, 3, 4, 5].map((i) => (
                                        <div key={i} className={`h-1 flex-1 rounded ${i <= strength ? strengthColors[strength] : 'bg-gray-200'}`}></div>
                                    ))}
                                </div>
                                <p className="text-xs text-gray-600">Force : {strengthLabels[strength]}</p>
                            </div>
                        )}
                        {errors.password && <p className="text-xs text-red-600 mt-1">{errors.password}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Confirmer *</label>
                        <input
                            type={showPassword ? 'text' : 'password'}
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className="w-full rounded border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            required
                        />
                        {errors.password_confirmation && <p className="text-xs text-red-600 mt-1">{errors.password_confirmation}</p>}
                    </div>

                    <label className="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" checked={showPassword} onChange={(e) => setShowPassword(e.target.checked)} />
                        Afficher les mots de passe
                    </label>

                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                        <strong>📋 Règles :</strong>
                        <ul className="mt-1 list-disc list-inside space-y-0.5">
                            <li className={data.password.length >= 8 ? 'text-green-700' : ''}>Au moins 8 caractères</li>
                            <li className={/[A-Z]/.test(data.password) && /[a-z]/.test(data.password) ? 'text-green-700' : ''}>Majuscules et minuscules</li>
                            <li className={/\d/.test(data.password) ? 'text-green-700' : ''}>Au moins 1 chiffre</li>
                            <li className={/[!@#$%&*?]/.test(data.password) ? 'text-green-700' : ''}>Au moins 1 symbole (!@#$%&*?)</li>
                        </ul>
                    </div>

                    <button
                        type="submit"
                        disabled={processing || strength < 3}
                        className="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {processing ? 'Modification...' : '✓ Valider et accéder à mon compte'}
                    </button>
                </form>
            </div>
        </div>
    );
}
