import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { PageProps } from '@/types';
import { User, Lock, Trash2, Settings } from 'lucide-react';

export default function Edit({ mustVerifyEmail, status }: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <ErpLayout>
            <Head title="Mon Profil" />

            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* HEADER */}
                    <div className="mb-8">
                        <div className="flex items-center gap-3 mb-2">
                            <div className="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                                <Settings className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Mon Profil</h1>
                        </div>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Gérez vos informations personnelles, votre mot de passe et vos préférences.
                        </p>
                    </div>

                    {/* CARTES */}
                    <div className="space-y-6">
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div className="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                                <User className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">Informations personnelles</h2>
                            </div>
                            <div className="p-6">
                                <UpdateProfileInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div className="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                                <Lock className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">Mot de passe</h2>
                            </div>
                            <div className="p-6">
                                <UpdatePasswordForm />
                            </div>
                        </div>

                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-red-200 dark:border-red-900/50 overflow-hidden">
                            <div className="px-6 py-4 border-b border-red-100 dark:border-red-900/50 flex items-center gap-3">
                                <Trash2 className="h-5 w-5 text-red-600 dark:text-red-400" />
                                <h2 className="text-lg font-semibold text-red-700 dark:text-red-400">Zone de danger</h2>
                            </div>
                            <div className="p-6">
                                <DeleteUserForm />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ErpLayout>
    );
}
