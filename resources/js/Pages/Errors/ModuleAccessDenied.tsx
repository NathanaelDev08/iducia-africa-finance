import { Head, Link, usePage } from '@inertiajs/react';

export default function ModuleAccessDenied() {
    const { auth } = usePage<any>().props;
    const modules = auth?.modules || [];

    return (
        <>
            <Head title="Accès refusé" />
            <div className="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center p-4">
                <div className="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div className="bg-gradient-to-r from-red-500 to-orange-500 p-8 text-white text-center">
                        <div className="text-6xl mb-4">🔒</div>
                        <h1 className="text-3xl font-bold">Accès refusé</h1>
                        <p className="mt-2 opacity-90">Vous n'avez pas les permissions nécessaires</p>
                    </div>

                    <div className="p-8">
                        <div className="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded">
                            <p className="text-sm text-amber-800">
                                <strong>⚠️ Information :</strong> Ce module ne fait pas partie de vos autorisations.
                                Contactez votre administrateur pour obtenir un accès.
                            </p>
                        </div>

                        {modules.length > 0 && (
                            <div className="mb-6">
                                <h2 className="font-semibold text-gray-700 mb-3">📋 Vos modules autorisés :</h2>
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    {modules.map((module: any) => (
                                        <Link
                                            key={module.code}
                                            href={module.route}
                                            className="flex items-center gap-2 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition border border-blue-200"
                                        >
                                            <span className="text-xl">{module.icon}</span>
                                            <span className="text-sm font-medium text-blue-900">{module.name}</span>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}

                        <div className="flex flex-col sm:flex-row gap-3 justify-center pt-4 border-t">
                            <Link
                                href="/dashboard"
                                className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                            >
                                🏠 Retour au tableau de bord
                            </Link>
                            <button
                                onClick={() => window.history.back()}
                                className="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium"
                            >
                                ← Page précédente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
