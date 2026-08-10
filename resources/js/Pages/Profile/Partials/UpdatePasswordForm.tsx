import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';
import { Eye, EyeOff, Lock, CheckCircle } from 'lucide-react';

type PasswordKey = 'current' | 'new' | 'confirm';

export default function UpdatePasswordForm({ className = '' }: { className?: string }) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);
    const [showPasswords, setShowPasswords] = useState<Record<PasswordKey, boolean>>({
        current: false,
        new: false,
        confirm: false,
    });

    const { data, setData, put, errors, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => {
                setData({ current_password: '', password: '', password_confirmation: '' });
            },
            onError: (errors) => {
                if (errors.password) passwordInput.current?.focus();
                else if (errors.current_password) currentPasswordInput.current?.focus();
            },
        });
    };

    const toggleShow = (key: PasswordKey) => {
        setShowPasswords((p) => ({ ...p, [key]: !p[key] }));
    };

    const PasswordField = ({
        id,
        label,
        value,
        onChange,
        error,
        inputRef,
        autoComplete,
        fieldKey,
    }: {
        id: string;
        label: string;
        value: string;
        onChange: (v: string) => void;
        error?: string;
        inputRef?: React.RefObject<HTMLInputElement>;
        autoComplete: string;
        fieldKey: PasswordKey;
    }) => (
        <div>
            <InputLabel htmlFor={id} value={label} />
            <div className="mt-1 relative rounded-md shadow-sm">
                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <Lock className="h-5 w-5 text-gray-400" />
                </div>
                <TextInput
                    id={id}
                    ref={inputRef}
                    type={showPasswords[fieldKey] ? 'text' : 'password'}
                    className="block w-full pl-10 pr-10"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    autoComplete={autoComplete}
                />
                <button
                    type="button"
                    onClick={() => toggleShow(fieldKey)}
                    className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                >
                    {showPasswords[fieldKey] ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                </button>
            </div>
            <InputError className="mt-2" message={error} />
        </div>
    );

    return (
        <section className={className}>
            <div className="mb-6">
                <h3 className="text-sm font-medium text-gray-900 dark:text-gray-100">Sécurité du compte</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Assurez-vous d'utiliser un mot de passe long et aléatoire pour sécuriser votre compte.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-6">
                <PasswordField
                    id="current_password"
                    label="Mot de passe actuel"
                    value={data.current_password}
                    onChange={(v) => setData('current_password', v)}
                    error={errors.current_password}
                    inputRef={currentPasswordInput}
                    autoComplete="current-password"
                    fieldKey="current"
                />

                <PasswordField
                    id="password"
                    label="Nouveau mot de passe"
                    value={data.password}
                    onChange={(v) => setData('password', v)}
                    error={errors.password}
                    inputRef={passwordInput}
                    autoComplete="new-password"
                    fieldKey="new"
                />

                <PasswordField
                    id="password_confirmation"
                    label="Confirmer le nouveau mot de passe"
                    value={data.password_confirmation}
                    onChange={(v) => setData('password_confirmation', v)}
                    error={errors.password_confirmation}
                    autoComplete="new-password"
                    fieldKey="confirm"
                />

                <div className="p-4 rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <p className="text-sm text-blue-800 dark:text-blue-300">
                        💡 <strong>Conseil :</strong> Utilisez au moins 8 caractères avec des majuscules, minuscules, chiffres et symboles.
                    </p>
                </div>

                <div className="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <PrimaryButton disabled={processing}>Mettre à jour le mot de passe</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <div className="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                            <CheckCircle className="h-4 w-4" />
                            Mot de passe mis à jour.
                        </div>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
