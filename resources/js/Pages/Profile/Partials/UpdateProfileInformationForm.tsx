import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useRef, useState } from 'react';
import { Camera, Mail, User, CheckCircle } from 'lucide-react';

export default function UpdateProfileInformationForm({ mustVerifyEmail, status, className = '' }: { mustVerifyEmail: boolean; status?: string; className?: string }) {
    const user = usePage().props.auth.user;
    const avatar = (user as any).avatar_url as string | undefined;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: user.name,
        email: user.email,
    });

    const [avatarPreview, setAvatarPreview] = useState<string | null>(avatar || null);
    const [uploading, setUploading] = useState(false);
    const [uploadMsg, setUploadMsg] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const fileRef = useRef<HTMLInputElement>(null);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Validation locale
        if (file.size > 2 * 1024 * 1024) {
            setUploadMsg({ type: 'error', text: 'Le fichier est trop volumineux (max 2 Mo)' });
            return;
        }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            setUploadMsg({ type: 'error', text: 'Format non supporté (JPG, PNG ou WEBP uniquement)' });
            return;
        }

        // Prévisualisation
        const reader = new FileReader();
        reader.onload = (ev) => setAvatarPreview(ev.target?.result as string);
        reader.readAsDataURL(file);

        // Upload
        const fd = new FormData();
        fd.append('avatar', file);
        fd.append('_method', 'PATCH');
        setUploading(true);
        setUploadMsg(null);

        window.axios.post(route('profile.avatar'), fd, {
            headers: {
                'Accept': 'application/json',
                'X-Inertia': 'true',
            },
        })
            .then(() => {
                setUploadMsg({ type: 'success', text: 'Photo de profil mise à jour !' });
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(() => setUploadMsg({ type: 'error', text: 'Erreur lors de l\'upload' }))
            .finally(() => setUploading(false));
    };

    return (
        <section className={className}>
            <div className="mb-6">
                <h3 className="text-sm font-medium text-gray-900 dark:text-gray-100">Photo de profil</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Cette photo apparaîtra dans le menu en haut à droite de l'application.
                </p>
            </div>

            {/* AVATAR */}
            <div className="flex items-start gap-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div className="relative group">
                    {avatarPreview ? (
                        <img src={avatarPreview} alt="avatar" className="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md" />
                    ) : (
                        <div className="h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-md">
                            {user.name.charAt(0).toUpperCase()}
                        </div>
                    )}
                    <button
                        type="button"
                        onClick={() => fileRef.current?.click()}
                        disabled={uploading}
                        className="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer disabled:cursor-not-allowed"
                    >
                        <Camera className="h-6 w-6 text-white" />
                    </button>
                    <input ref={fileRef} type="file" accept="image/jpeg,image/png,image/webp" onChange={handleAvatarChange} className="hidden" />
                </div>

                <div className="flex-1">
                    <button
                        type="button"
                        onClick={() => fileRef.current?.click()}
                        disabled={uploading}
                        className="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 rounded-md hover:bg-indigo-50 dark:hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {uploading ? (
                            <span className="flex items-center gap-2">
                                <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Téléversement...
                            </span>
                        ) : '📸 Changer la photo'}
                    </button>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Formats acceptés : JPG, PNG, WEBP · Taille max : 2 Mo
                    </p>

                    {uploadMsg && (
                        <div className={`mt-3 flex items-center gap-2 px-3 py-2 rounded-md text-sm ${
                            uploadMsg.type === 'success'
                                ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800'
                                : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800'
                        }`}>
                            {uploadMsg.type === 'success' ? <CheckCircle className="h-4 w-4" /> : null}
                            {uploadMsg.text}
                        </div>
                    )}
                </div>
            </div>

            {/* FORMULAIRE */}
            <form onSubmit={submit} className="mt-8 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Nom complet" />
                    <div className="mt-1 relative rounded-md shadow-sm">
                        <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <User className="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput
                            id="name"
                            className="block w-full pl-10"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            isFocused
                            autoComplete="name"
                        />
                    </div>
                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Adresse email" />
                    <div className="mt-1 relative rounded-md shadow-sm">
                        <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <Mail className="h-5 w-5 text-gray-400" />
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            className="block w-full pl-10"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoComplete="username"
                        />
                    </div>
                    <InputError className="mt-2" message={errors.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div className="p-4 rounded-md bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                        <p className="text-sm text-yellow-800 dark:text-yellow-300">
                            Votre adresse email n'est pas vérifiée.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="ml-1 font-medium underline hover:text-yellow-900 dark:hover:text-yellow-200"
                            >
                                Cliquez ici pour renvoyer l'email de vérification.
                            </Link>
                        </p>
                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                                Un nouveau lien de vérification a été envoyé à votre adresse email.
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <PrimaryButton disabled={processing}>Enregistrer les modifications</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <div className="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                            <CheckCircle className="h-4 w-4" />
                            Enregistré.
                        </div>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
