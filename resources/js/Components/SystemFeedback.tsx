import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

// ═══════════ GARDE DE CONFIRMATION AVANT SUPPRESSION ═══════════
// Enrichit TOUTES les boîtes de confirmation avec l'impact de l'action
const nativeConfirm = window.confirm.bind(window);
let patched = false;
function patchConfirm() {
    if (patched) return;
    patched = true;
    (window as any).confirm = (message?: string) => {
        const msg = String(message || '');
        const low = msg.toLowerCase();
        let impact = '';
        if (low.includes('supprim') || low.includes('delete') || low.includes('détruire')) {
            impact =
                '\n\n⚠️ IMPACT DE CETTE ACTION :\n' +
                '• Suppression DÉFINITIVE — aucune annulation possible.\n' +
                '• Les données liées (calculs, historiques, rapports) seront affectées.\n' +
                '• Les documents PDF déjà générés ne pourront plus être régénérés.';
        }
        if (low.includes('bulletin') || low.includes('paie')) {
            impact += '\n• Impacte la paie : totaux, cotisations CNPS et écritures comptables liées.';
        }
        if (low.includes('facture')) {
            impact += '\n• Impacte la comptabilité : totaux, TVA et trésorerie liés.';
        }
        if (low.includes('entreprise') || low.includes('société') || low.includes('bloquer')) {
            impact += '\n• Tous les utilisateurs de cette entité perdront leur accès.';
        }
        if (low.includes('utilisateur')) {
            impact += '\n• Cet utilisateur ne pourra plus se connecter ni accéder aux données.';
        }
        return nativeConfirm(
            '🛑 CONFIRMATION REQUISE\n\n' + msg + impact + '\n\nOK = confirmer l\'action · Annuler = abandonner'
        );
    };
}
patchConfirm();

interface Toast { id: number; type: 'success' | 'error' | 'info'; text: string; }

// ═══════════ POP-UPS (TOASTS) APRÈS CHAQUE ACTION ═══════════
export default function SystemFeedback() {
    const [toasts, setToasts] = useState<Toast[]>([]);
    const flash: any = (usePage().props as any).flash || {};

    useEffect(() => {
        patchConfirm();
        const items: Toast[] = [];
        if (flash.success) items.push({ id: Date.now() + 1, type: 'success', text: flash.success });
        if (flash.error) items.push({ id: Date.now() + 2, type: 'error', text: flash.error });
        if (flash.info) items.push({ id: Date.now() + 3, type: 'info', text: flash.info });
        if (items.length) {
            setToasts((t) => [...t, ...items]);
            items.forEach((it) =>
                setTimeout(() => setToasts((t) => t.filter((x) => x.id !== it.id)), 5000)
            );
        }
    }, [flash.success, flash.error, flash.info]);

    if (toasts.length === 0) return null;

    const styles: Record<string, string> = {
        success: 'bg-green-600 border-green-700',
        error: 'bg-red-600 border-red-700',
        info: 'bg-blue-600 border-blue-700',
    };
    const icons: Record<string, string> = { success: '✅', error: '⛔', info: 'ℹ️' };

    return (
        <div className="fixed top-5 right-5 z-[999] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
            {toasts.map((t) => (
                <div
                    key={t.id}
                    className={`pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-2xl border text-white text-sm font-medium animate-[slidein_.3s_ease] ${styles[t.type]}`}
                    style={{ animation: 'sf-slidein .3s ease' }}
                >
                    <span className="text-lg leading-none">{icons[t.type]}</span>
                    <span className="flex-1">{t.text}</span>
                    <button
                        type="button"
                        onClick={() => setToasts((x) => x.filter((y) => y.id !== t.id))}
                        className="opacity-70 hover:opacity-100 text-base leading-none"
                    >
                        ✕
                    </button>
                </div>
            ))}
            <style>{'@keyframes sf-slidein{from{transform:translateX(60px);opacity:0}to{transform:translateX(0);opacity:1}}'}</style>
        </div>
    );
}
