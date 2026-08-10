import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';
import { AlertTriangle, Trash2, X } from 'lucide-react';

export default function DeleteUserForm({ className = '' }: { className?: string }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef<HTMLInputElement>(null);

    const { data, setData, delete: destroy, processing, reset, errors } = useForm({ password: '' });

    const confirmUserDeletion = () => setConfirmingUserDeletion(true);

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();
        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        reset();
    };

    return (
        <section className={className}>
            <div className="mb-6">
                <h3 className="text-sm font-medium text-red-700 dark:text-red-400">Action irréversible</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Une fois votre compte supprimé, toutes ses données seront définitivement effacées.
                </p>
            </div>

            <div className="p-4 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/30">
                <div className="flex gap-3">
                    <AlertTriangle className="h-5 w-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                    <div className="flex-1">
                        <p className="text-sm text-red-800 dark:text-red-300">
                            Avant de supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.
                        </p>
                        <button
                            onClick={confirmUserDeletion}
                            className="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            <Trash2 className="h-4 w-4" />
                            Supprimer mon compte
                        </button>
                    </div>
                </div>
            </div>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form onSubmit={deleteUser} className="p-6">
                    <div className="flex justify-center mb-4">
                        <div className="p-3 bg-red-100 dark:bg-red-900/30 rounded-full">
                            <AlertTriangle className="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                    </div>

                    <h2 className="text-lg text-center font-bold text-gray-900 dark:text-gray-100">
                        Êtes-vous sûr de vouloir supprimer votre compte ?
                    </h2>

                    <p className="mt-3 text-sm text-center text-gray-600 dark:text-gray-400">
                        Une fois votre compte supprimé, toutes ses données seront définitivement effacées.
                        Veuillez entrer votre mot de passe pour confirmer.
                    </p>

                    <div className="mt-6">
                        <InputLabel htmlFor="password" value="Mot de passe" className="sr-only" />
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="mt-1 block w-full"
                            isFocused
                            placeholder="Votre mot de passe"
                        />
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            onClick={closeModal}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        >
                            <X className="h-4 w-4" />
                            Annuler
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition disabled:opacity-50"
                        >
                            <Trash2 className="h-4 w-4" />
                            {processing ? 'Suppression...' : 'Supprimer mon compte'}
                        </button>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
