export default function ApplicationLogo({ className = '' }: { className?: string }) {
    return (
        <img
            src="/images/logo.png"
            alt="FIDUCIA AFRIC – Conseils et Finances"
            className={'object-contain ' + className}
            style={{ height: '112px', width: 'auto' }}
        />
    );
}
