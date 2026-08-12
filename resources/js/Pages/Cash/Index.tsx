import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface CashRegister { id:number; period_start:string; period_end:string; opening_balance:number; closing_balance:number; status:string; transactions_count:number; }
interface CashTransaction { id:number; transaction_date:string; reference:string|null; description:string|null; type:'in'|'out'; amount:number; }
interface Props { cashRegisters:CashRegister[]; selectedCash:CashRegister|null; cashTransactions:CashTransaction[]; }
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index(p: Readonly<Props>) {
  const [selectedId, setSelectedId] = useState<number | null>(p.selectedCash?.id ?? (p.cashRegisters[0]?.id ?? null));
  const [registerModal, setRegisterModal] = useState(false);
  const [transactionModal, setTransactionModal] = useState(false);
  const [importing, setImporting] = useState(false);
  const [file, setFile] = useState<File|null>(null);
  const [flash] = useState((usePage().props as any).flash);
  const selectedRegister = p.cashRegisters.find((r) => r.id === selectedId) ?? null;
  const exportUrl = selectedId ? route('treasury.cash.export', { format: 'csv', register_id: selectedId }) : '#';

  const handleRegisterCreated = () => {
    setRegisterModal(false);
    router.reload();
  };

  const handleTransactionCreated = () => {
    setTransactionModal(false);
    router.reload();
  };

  const handleImportSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedId || !file) return;
    const data = new FormData();
    data.append('register_id', String(selectedId));
    data.append('file', file);
    router.post(route('treasury.cash.import'), data, { forceFormData: true, onSuccess: handleImportSubmitSuccess });
  };

  const handleImportSubmitSuccess = () => {
    setImporting(false);
    setFile(null);
    router.reload();
  };

  return (
    <ErpLayout>
      <Head title="Caisse" />
      <div className="py-6">
        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-gray-900">💰 Caisse</h1>
            <p className="mt-1 text-sm text-gray-500">Gérez les sessions de caisse, les transactions et import/export de fichiers.</p>
          </div>

          {flash?.success && <div className="p-3 mb-4 text-sm text-green-800 border border-green-200 rounded bg-green-50">✓ {flash.success}</div>}
          {flash?.error && <div className="p-3 mb-4 text-sm text-red-800 border border-red-200 rounded bg-red-50">✗ {flash.error}</div>}

          <div className="flex flex-wrap gap-3 mb-6">
            <button type="button" onClick={() => setRegisterModal(true)} className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">+ Session de caisse</button>
            <button type="button" onClick={() => setTransactionModal(true)} disabled={!selectedId} className="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">+ Transaction</button>
            <button type="button" onClick={() => setImporting(true)} disabled={!selectedId} className="px-4 py-2 text-sm text-white bg-yellow-600 rounded-md hover:bg-yellow-700 disabled:opacity-50">Importer</button>
            <a href={exportUrl} className={`px-4 py-2 text-sm text-white rounded-md ${selectedId ? 'bg-gray-800 hover:bg-gray-900' : 'bg-gray-400 cursor-not-allowed'}`}>
              Exporter CSV
            </a>
            <div className="ml-auto text-sm text-gray-600">{p.cashRegisters.length} session(s) | {selectedRegister?.transactions_count ?? 0} transaction(s)</div>
          </div>

          <div className="grid gap-4 lg:grid-cols-2">
            <div className="p-4 border rounded-lg bg-white shadow-sm">
              <h2 className="mb-4 text-sm font-semibold text-gray-900">Sessions de caisse</h2>
              <div className="space-y-2">
                {p.cashRegisters.length === 0 && <div className="text-sm text-gray-500">Aucune session de caisse.</div>}
                {p.cashRegisters.map((register) => (
                  <button key={register.id} type="button" onClick={() => setSelectedId(register.id)} className={`w-full text-left p-3 rounded-lg border ${selectedId === register.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 bg-white'} hover:border-indigo-500`}>
                    <div className="text-sm font-semibold">{new Date(register.period_start).toLocaleDateString('fr-FR')} → {new Date(register.period_end).toLocaleDateString('fr-FR')}</div>
                    <div className="text-xs text-gray-500">Ouverture {formatMoney(register.opening_balance)} · Clôture {formatMoney(register.closing_balance)}</div>
                    <div className="text-xs text-gray-500">{register.transactions_count} transaction(s) · {register.status === 'closed' ? 'Fermée' : 'Brouillon'}</div>
                  </button>
                ))}
              </div>
            </div>
            <div className="p-4 border rounded-lg bg-white shadow-sm">
              <h2 className="mb-4 text-sm font-semibold text-gray-900">Détail session</h2>
              {!selectedRegister && <div className="text-sm text-gray-500">Sélectionnez une session pour voir ses transactions.</div>}
              {selectedRegister && (
                <div className="space-y-3 text-sm text-gray-700">
                  <div>Période : {new Date(selectedRegister.period_start).toLocaleDateString('fr-FR')} → {new Date(selectedRegister.period_end).toLocaleDateString('fr-FR')}</div>
                  <div>Solde ouverture : {formatMoney(selectedRegister.opening_balance)}</div>
                  <div>Solde clôture : {formatMoney(selectedRegister.closing_balance)}</div>
                  <div>Statut : {selectedRegister.status === 'closed' ? 'Fermée' : 'Brouillon'}</div>
                </div>
              )}
            </div>
          </div>

          <div className="mt-6 overflow-x-auto bg-white border rounded-lg shadow-sm">
            <table className="w-full text-sm">
              <thead className="border-b bg-gray-50">
                <tr>
                  <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th>
                  <th className="p-2 text-left text-xs text-gray-600 uppercase">Réf</th>
                  <th className="p-2 text-left text-xs text-gray-600 uppercase">Description</th>
                  <th className="p-2 text-right text-xs text-gray-600 uppercase">Type</th>
                  <th className="p-2 text-right text-xs text-gray-600 uppercase">Montant</th>
                  <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {!selectedRegister && <tr><td colSpan={6} className="p-8 text-center text-gray-500">Sélectionnez une session pour afficher les transactions.</td></tr>}
                {selectedRegister && p.cashTransactions.length === 0 && <tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucune transaction pour cette session.</td></tr>}
                {selectedRegister && p.cashTransactions.map((tx) => (
                  <tr key={tx.id} className="hover:bg-gray-50">
                    <td className="p-2 text-xs">{new Date(tx.transaction_date).toLocaleDateString('fr-FR')}</td>
                    <td className="p-2 font-mono text-xs">{tx.reference || '—'}</td>
                    <td className="p-2 text-xs">{tx.description || '—'}</td>
                    <td className="p-2 text-right text-xs capitalize">{tx.type === 'in' ? 'Entrée' : 'Sortie'}</td>
                    <td className="p-2 font-mono text-right">{formatMoney(tx.amount)}</td>
                    <td className="p-2 text-right"><button type="button" onClick={() => router.delete(route('treasury.cash.transactions.destroy', tx.id))} className="text-xs text-red-600 hover:underline">Supprimer</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {registerModal && <CashRegisterModal onClose={() => setRegisterModal(false)} onSuccess={handleRegisterCreated} />}
          {transactionModal && selectedRegister && <CashTransactionModal registerId={selectedRegister.id} onClose={() => setTransactionModal(false)} onSuccess={handleTransactionCreated} />}
          {importing && selectedRegister && (
            <CashImportModal registerId={selectedRegister.id} onClose={() => setImporting(false)} onSuccess={handleImportSubmitSuccess} file={file} setFile={setFile} />
          )}
        </div>
      </div>
    </ErpLayout>
  );
}

function CashRegisterModal({ onClose, onSuccess }: Readonly<{ onClose: () => void; onSuccess: () => void }>) {
  const [start, setStart] = useState(new Date().toISOString().slice(0, 8) + '01');
  const [end, setEnd] = useState(new Date().toISOString().slice(0, 10));
  const [opening, setOpening] = useState(0);
  const [closing, setClosing] = useState(0);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    router.post(route('treasury.cash.registers.store'), {
      period_start: start,
      period_end: end,
      opening_balance: opening,
      closing_balance: closing,
    }, {
      onSuccess: onSuccess,
    });
  };

  return (
    <ModalShell title="➕ Session de caisse" onClose={onClose}>
      <form onSubmit={submit} className="space-y-3">
        <div className="grid grid-cols-2 gap-3">
          <div>
            <label htmlFor="cash-start" className="block mb-1 text-sm font-medium text-gray-700">Début *</label>
            <input id="cash-start" type="date" value={start} onChange={(e) => setStart(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required />
          </div>
          <div>
            <label htmlFor="cash-end" className="block mb-1 text-sm font-medium text-gray-700">Fin *</label>
            <input id="cash-end" type="date" value={end} onChange={(e) => setEnd(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required />
          </div>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div>
            <label htmlFor="cash-opening" className="block mb-1 text-sm font-medium text-gray-700">Solde ouverture</label>
            <input id="cash-opening" type="number" value={opening} onChange={(e) => setOpening(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md" />
          </div>
          <div>
            <label htmlFor="cash-closing" className="block mb-1 text-sm font-medium text-gray-700">Solde clôture</label>
            <input id="cash-closing" type="number" value={closing} onChange={(e) => setClosing(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md" />
          </div>
        </div>
        <ModalFooter onClose={onClose} />
      </form>
    </ModalShell>
  );
}

function CashTransactionModal({ registerId, onClose, onSuccess }: Readonly<{ registerId:number; onClose:()=>void; onSuccess:()=>void }>) {
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [reference, setReference] = useState('');
  const [description, setDescription] = useState('');
  const [type, setType] = useState<'in'|'out'>('in');
  const [amount, setAmount] = useState(0);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    router.post(route('treasury.cash.transactions.store'), {
      cash_register_id: registerId,
      transaction_date: date,
      reference,
      description,
      type,
      amount,
    }, {
      onSuccess,
    });
  };

  return (
    <ModalShell title="➕ Transaction de caisse" onClose={onClose}>
      <form onSubmit={submit} className="space-y-3">
        <div>
          <label htmlFor="cash-date" className="block mb-1 text-sm font-medium text-gray-700">Date *</label>
          <input id="cash-date" type="date" value={date} onChange={(e) => setDate(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required />
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div>
            <label htmlFor="cash-reference" className="block mb-1 text-sm font-medium text-gray-700">Référence</label>
            <input id="cash-reference" value={reference} onChange={(e) => setReference(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" />
          </div>
          <div>
            <label htmlFor="cash-type" className="block mb-1 text-sm font-medium text-gray-700">Type</label>
            <select id="cash-type" value={type} onChange={(e) => setType(e.target.value as 'in'|'out')} className="w-full text-sm border-gray-300 rounded-md">
              <option value="in">Entrée</option>
              <option value="out">Sortie</option>
            </select>
          </div>
        </div>
        <div>
          <label htmlFor="cash-amount" className="block mb-1 text-sm font-medium text-gray-700">Montant *</label>
          <input id="cash-amount" type="number" min="0.01" step="0.01" value={amount} onChange={(e) => setAmount(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required />
        </div>
        <div>
          <label htmlFor="cash-description" className="block mb-1 text-sm font-medium text-gray-700">Description</label>
          <input id="cash-description" value={description} onChange={(e) => setDescription(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" />
        </div>
        <ModalFooter onClose={onClose} />
      </form>
    </ModalShell>
  );
}

function CashImportModal({ registerId, onClose, onSuccess, file, setFile }: Readonly<{ registerId:number; onClose:()=>void; onSuccess:()=>void; file:File|null; setFile:(file:File|null)=>void }>) {
  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!file) return;
    const data = new FormData();
    data.append('register_id', String(registerId));
    data.append('file', file);
    router.post(route('treasury.cash.import'), data, { forceFormData: true, onSuccess });
  };

  return (
    <ModalShell title="Importer des transactions" onClose={onClose}>
      <form onSubmit={submit} className="space-y-3">
        <div>
          <label htmlFor="cash-import-file" className="block mb-1 text-sm font-medium text-gray-700">Fichier CSV / JSON</label>
          <input id="cash-import-file" type="file" accept=".csv,.txt,.json" onChange={(e) => setFile(e.target.files?.[0] ?? null)} className="w-full text-sm" required />
        </div>
        <ModalFooter onClose={onClose} />
      </form>
    </ModalShell>
  );
}

function ModalShell({ title, children, onClose }: Readonly<{ title:string; children:React.ReactNode; onClose:()=>void }>) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <button type="button" className="absolute inset-0 bg-black/50" onClick={onClose} aria-label="Fermer la fenêtre" />
      <div className="relative z-10 bg-white rounded-lg shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div className="sticky top-0 z-10 p-5 bg-white border-b"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
        <div className="p-5">{children}</div>
      </div>
    </div>
  );
}

function ModalFooter({ onClose }: Readonly<{ onClose: ()=>void }>) {
  return (
    <div className="flex justify-end gap-3 pt-2 mt-4 border-t">
      <button type="button" onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
      <button type="submit" className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Enregistrer</button>
    </div>
  );
}
