import { useForm } from '@inertiajs/react';

export default function SettingsCrud({ tab }: any) {
    return (
        <div className="mt-6">
            {tab === 'taxes' && <TaxForm />}
            {tab === 'payroll' && <PayrollForms />}
            {tab === 'accounting' && <JournalForm />}
            {tab === 'users' && <p className="rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">💡 Utilisez l'onglet 🛡️ Gestion utilisateurs pour activer/désactiver un compte.</p>}
        </div>
    );
}

function TaxForm() {
    const { data, setData, post, processing } = useForm({ name: '', code: '', type: 'vat' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post('/parametrage/crud/taxes'); }} className="rounded-lg bg-white p-6 shadow">
            <h3 className="mb-4 font-semibold text-gray-800">➕ Ajouter une taxe</h3>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                <input placeholder="Nom (ex: TVA 18%)" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="rounded-md border-gray-300 px-3 py-2" />
                <input placeholder="Code (ex: TVA_18)" value={data.code} onChange={(e) => setData('code', e.target.value)} required className="rounded-md border-gray-300 px-3 py-2" />
                <select value={data.type} onChange={(e) => setData('type', e.target.value)} className="rounded-md border-gray-300 px-3 py-2">
                    <option value="vat">TVA</option><option value="other">Autre</option>
                </select>
            </div>
            <button disabled={processing} className="mt-4 rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50">Ajouter la taxe</button>
        </form>
    );
}

function PayrollForms() {
    const c = useForm({ name: '', code: '', organism: 'CNPS' });
    const p = useForm({ name: '', code: '', type: 'earning' });
    return (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
            <form onSubmit={(e) => { e.preventDefault(); c.post('/parametrage/crud/contributions'); }} className="rounded-lg bg-white p-6 shadow">
                <h3 className="mb-4 font-semibold text-gray-800">➕ Ajouter une cotisation</h3>
                <div className="space-y-3">
                    <input placeholder="Nom (ex: CNPS salarié)" value={c.data.name} onChange={(e) => c.setData('name', e.target.value)} required className="w-full rounded-md border-gray-300 px-3 py-2" />
                    <input placeholder="Code" value={c.data.code} onChange={(e) => c.setData('code', e.target.value)} required className="w-full rounded-md border-gray-300 px-3 py-2" />
                    <input placeholder="Organisme" value={c.data.organism} onChange={(e) => c.setData('organism', e.target.value)} className="w-full rounded-md border-gray-300 px-3 py-2" />
                </div>
                <button disabled={c.processing} className="mt-4 rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50">Ajouter</button>
            </form>
            <form onSubmit={(e) => { e.preventDefault(); p.post('/parametrage/crud/pay-items'); }} className="rounded-lg bg-white p-6 shadow">
                <h3 className="mb-4 font-semibold text-gray-800">➕ Ajouter une rubrique de paie</h3>
                <div className="space-y-3">
                    <input placeholder="Nom (ex: Prime de transport)" value={p.data.name} onChange={(e) => p.setData('name', e.target.value)} required className="w-full rounded-md border-gray-300 px-3 py-2" />
                    <input placeholder="Code" value={p.data.code} onChange={(e) => p.setData('code', e.target.value)} required className="w-full rounded-md border-gray-300 px-3 py-2" />
                    <select value={p.data.type} onChange={(e) => p.setData('type', e.target.value)} className="w-full rounded-md border-gray-300 px-3 py-2">
                        <option value="earning">Gain</option><option value="deduction">Retenue</option>
                    </select>
                </div>
                <button disabled={p.processing} className="mt-4 rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50">Ajouter</button>
            </form>
        </div>
    );
}

function JournalForm() {
    const { data, setData, post, processing } = useForm({ code: '', name: '', type: 'misc' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post('/parametrage/crud/journals'); }} className="rounded-lg bg-white p-6 shadow">
            <h3 className="mb-4 font-semibold text-gray-800">➕ Ajouter un journal</h3>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                <input placeholder="Code (ex: VE)" value={data.code} onChange={(e) => setData('code', e.target.value)} required className="rounded-md border-gray-300 px-3 py-2" />
                <input placeholder="Nom (ex: Ventes)" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="rounded-md border-gray-300 px-3 py-2" />
                <select value={data.type} onChange={(e) => setData('type', e.target.value)} className="rounded-md border-gray-300 px-3 py-2">
                    <option value="sale">Ventes</option><option value="purchase">Achats</option>
                    <option value="bank">Banque</option><option value="cash">Caisse</option>
                    <option value="payroll">Paie</option><option value="misc">Divers</option>
                </select>
            </div>
            <button disabled={processing} className="mt-4 rounded-md bg-gray-900 px-4 py-2 text-white disabled:opacity-50">Ajouter le journal</button>
        </form>
    );
}
