import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Statement { id:number; account:string; period_start:string; period_end:string; opening_balance:number; closing_balance:number; status:string; lines_count:number; matched_count:number; }
interface Line { id:number; transaction_date:string; reference:string|null; description:string|null; debit:number; credit:number; net:number; status:string; is_matched:boolean; }
interface BankItem { id:number; entry_date:string; reference:string; description:string; debit:number; credit:number; net:number; }
interface CashRegister { id:number; period_start:string; period_end:string; opening_balance:number; closing_balance:number; status:string; transactions_count:number; }
interface CashTransaction { id:number; transaction_date:string; reference:string|null; description:string|null; type:'in'|'out'; amount:number; }
interface Props { statements:Statement[]; selected:Statement|null; lines:Line[]; unmatchedItems:BankItem[]; bankAccounts:{id:number;number:string;name:string}[]; cashRegisters:CashRegister[]; selectedCash:CashRegister|null; cashTransactions:CashTransaction[]; initialTab:string; }
type TabKey='statements'|'reconciliation'|'cash';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index(p: Readonly<Props>){
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'statements');
  useEffect(()=>{
    setTab((p.initialTab as TabKey) || 'statements');
  }, [p.initialTab]);
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  return (
    <ErpLayout>
      <Head title="Trésorerie"/>
      <div className="py-6"><div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🏦 Trésorerie</h1>
        <p className="mt-1 text-sm text-gray-500">Rapprochement bancaire : relevé ↔ écritures 521</p></div>
        <div className="bg-white border-b rounded-t-lg shadow-sm"><nav className="flex">
          {([{key:'statements',label:'Relevés',icon:'🏦'},{key:'reconciliation',label:'Rapprochement',icon:'🔗'},{key:'cash',label:'Caisse',icon:'💰'}] as {key:TabKey;label:string;icon:string}[]).map(t=>{const a=tab===t.key;return(
            <button type="button" key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>
        <div className="p-6 bg-white rounded-b-lg shadow-sm">
          {tab==='statements'&&<StatementsTab statements={p.statements} bankAccounts={p.bankAccounts}/>}
          {tab==='reconciliation'&&<ReconciliationTab selected={p.selected} lines={p.lines} unmatchedItems={p.unmatchedItems} statements={p.statements}/>}
          {tab==='cash'&&<CashTab cashRegisters={p.cashRegisters} selectedCash={p.selectedCash} cashTransactions={p.cashTransactions}/>}

        </div>
      </div></div>
    </ErpLayout>
  );
}

function StatementsTab({statements,bankAccounts}: Readonly<{statements:Statement[];bankAccounts:any[]}>){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Statement|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button type="button" onClick={()=>setModal(true)} className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">+ Relevé</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="border-b bg-gray-50"><tr>
        <th className="p-2 text-xs text-left text-gray-600 uppercase">Compte</th><th className="p-2 text-xs text-left text-gray-600 uppercase">Du</th>
        <th className="p-2 text-xs text-left text-gray-600 uppercase">Au</th><th className="p-2 text-xs text-right text-gray-600 uppercase">Solde ouv.</th>
        <th className="p-2 text-xs text-right text-gray-600 uppercase">Solde clô.</th><th className="p-2 text-xs text-center text-gray-600 uppercase">Lignes</th>
        <th className="p-2 text-xs text-center text-gray-600 uppercase">Rapprochées</th><th className="p-2 text-xs text-center text-gray-600 uppercase">Statut</th>
        <th className="p-2 text-xs text-right text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{statements.length===0?<tr><td colSpan={9} className="p-8 text-center text-gray-500">Aucun relevé.</td></tr>:statements.map(s=>(
        <tr key={s.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs">{s.account}</td>
          <td className="p-2 text-xs">{new Date(s.period_start).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-xs">{new Date(s.period_end).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 font-mono text-right">{formatMoney(s.opening_balance)}</td>
          <td className="p-2 font-mono text-right">{formatMoney(s.closing_balance)}</td>
          <td className="p-2 text-center">{s.lines_count}</td>
          <td className="p-2 font-semibold text-center text-green-700">{s.matched_count}</td>
          <td className="p-2 text-center">{s.status==='reconciled'?<span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Rapproché</span>:<span className="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Brouillon</span>}</td>
          <td className="p-2 text-right">
            <a href={route('treasury.index',{tab:'reconciliation',statement:s.id})} className="mr-3 text-xs text-indigo-600 hover:underline">🔗 Rapprocher</a>
            <button type="button" onClick={()=>setDel(s)} className="text-xs text-red-600 hover:underline">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal&&<StatementModal bankAccounts={bankAccounts} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={'relevé '+del.account} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('treasury.destroy',del.id))}/>}
  </div>);
}
function StatementModal({bankAccounts,onClose}: Readonly<{bankAccounts:any[];onClose:()=>void}>){
  const [accountId,setAccountId]=useState('');
  const [start,setStart]=useState(new Date().toISOString().slice(0,8)+'01');
  const [end,setEnd]=useState(new Date().toISOString().slice(0,10));
  const [opening,setOpening]=useState(0);const [closing,setClosing]=useState(0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('treasury.store'),{account_id:accountId||null,period_start:start,period_end:end,opening_balance:opening,closing_balance:closing},{onSuccess:onClose});};
  return (<ModalShell title="➕ Relevé bancaire" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label htmlFor="statement-account" className="block mb-1 text-sm font-medium text-gray-700">Compte banque</label>
      <select id="statement-account" value={accountId} onChange={e=>setAccountId(e.target.value)} className="w-full text-sm border-gray-300 rounded-md"><option value="">—</option>{bankAccounts.map((a:any)=><option key={a.id} value={a.id}>{a.number} - {a.name}</option>)}</select></div>
    <div className="grid grid-cols-2 gap-3">
      <div><label htmlFor="statement-start" className="block mb-1 text-sm font-medium text-gray-700">Début *</label><input id="statement-start" type="date" value={start} onChange={e=>setStart(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required/></div>
      <div><label htmlFor="statement-end" className="block mb-1 text-sm font-medium text-gray-700">Fin *</label><input id="statement-end" type="date" value={end} onChange={e=>setEnd(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required/></div></div>
    <div className="grid grid-cols-2 gap-3">
      <div><label htmlFor="statement-opening" className="block mb-1 text-sm font-medium text-gray-700">Solde ouverture</label><input id="statement-opening" type="number" value={opening} onChange={e=>setOpening(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></div>
      <div><label htmlFor="statement-closing" className="block mb-1 text-sm font-medium text-gray-700">Solde clôture</label><input id="statement-closing" type="number" value={closing} onChange={e=>setClosing(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></div></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}



function ReconciliationTab({selected,lines,unmatchedItems,statements}: Readonly<{selected:Statement|null;lines:Line[];unmatchedItems:BankItem[];statements:Statement[]}>){
  const [lineModal,setLineModal]=useState(false);
  const [matchFor,setMatchFor]=useState<Line|null>(null);
  if(!selected) return <div className="p-8 text-center text-gray-500">Créez d'abord un relevé dans l'onglet « Relevés ».</div>;
  const totalDebit=lines.reduce((s,l)=>s+l.debit,0);
  const totalCredit=lines.reduce((s,l)=>s+l.credit,0);
  const unmatched=lines.filter(l=>!l.is_matched).length;
  return (<div>
    <div className="flex flex-wrap items-center gap-3 mb-4">
      <select value={selected.id} onChange={e=>router.get(route('treasury.index'),{tab:'reconciliation',statement:e.target.value},{preserveState:true,preserveScroll:true,replace:true})} className="text-sm border-gray-300 rounded-md">
        {statements.map(s=><option key={s.id} value={s.id}>{s.account} · {new Date(s.period_start).toLocaleDateString('fr-FR')} → {new Date(s.period_end).toLocaleDateString('fr-FR')}</option>)}
      </select>
      <button type="button" onClick={()=>setLineModal(true)} className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">+ Ligne</button>
      <button type="button" onClick={()=>router.post(route('treasury.reconcile',selected.id))} disabled={unmatched>0} className="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">✅ Rapprocher le relevé</button>
      <div className="ml-auto text-sm text-gray-600">Débit {formatMoney(totalDebit)} · Crédit {formatMoney(totalCredit)} · <span className={unmatched>0?'text-yellow-700 font-semibold':'text-green-700 font-semibold'}>{unmatched} non rapprochée(s)</span></div>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="border-b bg-gray-50"><tr>
        <th className="p-2 text-xs text-left text-gray-600 uppercase">Date</th><th className="p-2 text-xs text-left text-gray-600 uppercase">Réf</th>
        <th className="p-2 text-xs text-left text-gray-600 uppercase">Description</th><th className="p-2 text-xs text-right text-gray-600 uppercase">Débit</th>
        <th className="p-2 text-xs text-right text-gray-600 uppercase">Crédit</th><th className="p-2 text-xs text-center text-gray-600 uppercase">État</th>
        <th className="p-2 text-xs text-right text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{lines.length===0?<tr><td colSpan={7} className="p-8 text-center text-gray-500">Aucune ligne. Ajoutez-les ou importez le relevé.</td></tr>:lines.map(l=>(
        <tr key={l.id} className="hover:bg-gray-50">
          <td className="p-2 text-xs">{new Date(l.transaction_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 font-mono text-xs">{l.reference||'—'}</td><td className="p-2 text-xs">{l.description||'—'}</td>
          <td className="p-2 font-mono text-right">{l.debit>0?formatMoney(l.debit):'—'}</td>
          <td className="p-2 font-mono text-right">{l.credit>0?formatMoney(l.credit):'—'}</td>
          <td className="p-2 text-center">{l.is_matched?<span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">🔗 Rapprochée</span>:<span className="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">À rapprocher</span>}</td>
          <td className="p-2 text-right">
            {l.is_matched
              ?<button type="button" onClick={()=>router.post(route('treasury.lines.unmatch',l.id))} className="mr-2 text-xs text-gray-600 hover:underline">Délier</button>
              :<button type="button" onClick={()=>setMatchFor(l)} className="mr-2 text-xs text-indigo-600 hover:underline">🔗 Rapprocher</button>}
            <button type="button" onClick={()=>router.delete(route('treasury.lines.destroy',l.id))} className="text-xs text-red-600 hover:underline">🗑</button>
          </td>
        </tr>))}</tbody></table></div>
    {lineModal&&<LineModal statementId={selected.id} onClose={()=>setLineModal(false)}/>}
    {matchFor&&<MatchModal line={matchFor} items={unmatchedItems} onClose={()=>setMatchFor(null)}/>}
  </div>);
}
function CashTab({cashRegisters,selectedCash,cashTransactions}: Readonly<{cashRegisters:CashRegister[];selectedCash:CashRegister|null;cashTransactions:CashTransaction[]}>) {
  const [selectedId,setSelectedId]=useState<number|null>(selectedCash?.id ?? (cashRegisters[0]?.id ?? null));
  const [registerModal,setRegisterModal]=useState(false);
  const [transactionModal,setTransactionModal]=useState(false);
  const [importing,setImporting]=useState(false);
  const [file,setFile]=useState<File|null>(null);
  const selectedRegister = cashRegisters.find((r)=>r.id===selectedId) ?? null;
  const exportUrl = selectedId ? route('treasury.cash.export', { format:'csv', register_id:selectedId }) : '#';

  useEffect(() => {
    setSelectedId(selectedCash?.id ?? (cashRegisters[0]?.id ?? null));
  }, [selectedCash?.id, cashRegisters]);

  const handleRegisterCreated = () => { setRegisterModal(false); router.reload(); };
  const handleTransactionCreated = () => { setTransactionModal(false); router.reload(); };
  const handleImportSubmitSuccess = () => { setImporting(false); setFile(null); router.reload(); };

  return (<div>
    <div className="flex flex-wrap gap-3 mb-6">
      <button type="button" onClick={()=>setRegisterModal(true)} className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">+ Session de caisse</button>
      <button type="button" onClick={()=>setTransactionModal(true)} disabled={!selectedId} className="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">+ Transaction</button>
      <button type="button" onClick={()=>setImporting(true)} disabled={!selectedId} className="px-4 py-2 text-sm text-white bg-yellow-600 rounded-md hover:bg-yellow-700 disabled:opacity-50">Importer</button>
      <a href={exportUrl} className={`px-4 py-2 text-sm text-white rounded-md ${selectedId ? 'bg-gray-800 hover:bg-gray-900' : 'bg-gray-400 cursor-not-allowed'}`}>
        Exporter CSV
      </a>
      <div className="ml-auto text-sm text-gray-600">{cashRegisters.length} session(s) | {selectedRegister?.transactions_count ?? 0} transaction(s)</div>
    </div>

    <div className="grid gap-4 lg:grid-cols-2">
      <div className="p-4 border rounded-lg bg-white shadow-sm">
        <h2 className="mb-4 text-sm font-semibold text-gray-900">Sessions de caisse</h2>
        <div className="space-y-2">
          {cashRegisters.length===0 && <div className="text-sm text-gray-500">Aucune session de caisse.</div>}
          {cashRegisters.map((register)=>(
            <button key={register.id} type="button" onClick={()=>router.get(route('treasury.index'), { tab:'cash', register: register.id }, { preserveState:true,preserveScroll:true,replace:true })} className={`w-full text-left p-3 rounded-lg border ${selectedId===register.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 bg-white'} hover:border-indigo-500`}>
              <div className="text-sm font-semibold">{new Date(register.period_start).toLocaleDateString('fr-FR')} → {new Date(register.period_end).toLocaleDateString('fr-FR')}</div>
              <div className="text-xs text-gray-500">Ouverture {formatMoney(register.opening_balance)} · Clôture {formatMoney(register.closing_balance)}</div>
              <div className="text-xs text-gray-500">{register.transactions_count} transaction(s) · {register.status === 'closed' ? 'Fermée' : 'Brouillon'}</div>
            </button>))}
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
          {selectedRegister && cashTransactions.length===0 && <tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucune transaction pour cette session.</td></tr>}
          {selectedRegister && cashTransactions.map((tx)=>(
            <tr key={tx.id} className="hover:bg-gray-50">
              <td className="p-2 text-xs">{new Date(tx.transaction_date).toLocaleDateString('fr-FR')}</td>
              <td className="p-2 font-mono text-xs">{tx.reference || '—'}</td>
              <td className="p-2 text-xs">{tx.description || '—'}</td>
              <td className="p-2 text-right text-xs capitalize">{tx.type==='in' ? 'Entrée' : 'Sortie'}</td>
              <td className="p-2 font-mono text-right">{formatMoney(tx.amount)}</td>
              <td className="p-2 text-right"><button type="button" onClick={()=>router.delete(route('treasury.cash.transactions.destroy', tx.id), { onSuccess: () => router.reload() })} className="text-xs text-red-600 hover:underline">Supprimer</button></td>
            </tr>))}
        </tbody>
      </table>
    </div>

    {registerModal&&<CashRegisterModal onClose={()=>setRegisterModal(false)} onSuccess={handleRegisterCreated} />}
    {transactionModal&&selectedRegister&&<CashTransactionModal registerId={selectedRegister.id} onClose={()=>setTransactionModal(false)} onSuccess={handleTransactionCreated} />}
    {importing&&selectedRegister&&(
      <CashImportModal registerId={selectedRegister.id} onClose={()=>setImporting(false)} onSuccess={handleImportSubmitSuccess} file={file} setFile={setFile} />
    )}
  </div>);
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
function LineModal({statementId,onClose}: Readonly<{statementId:number;onClose:()=>void}>){
  const [date,setDate]=useState(new Date().toISOString().slice(0,10));
  const [reference,setReference]=useState('');const [description,setDescription]=useState('');
  const [debit,setDebit]=useState(0);const [credit,setCredit]=useState(0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('treasury.lines.store'),{bank_statement_id:statementId,transaction_date:date,reference,description,debit,credit},{onSuccess:onClose});};
  return (<ModalShell title="➕ Ligne de relevé" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label htmlFor="line-date" className="block mb-1 text-sm font-medium text-gray-700">Date *</label><input id="line-date" type="date" value={date} onChange={e=>setDate(e.target.value)} className="w-full text-sm border-gray-300 rounded-md" required/></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Référence" htmlFor="line-reference"><input id="line-reference" value={reference} onChange={e=>setReference(e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></Field>
    <Field label="Description" htmlFor="line-description"><input id="line-description" value={description} onChange={e=>setDescription(e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></Field></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Débit (encaissement)" htmlFor="line-debit"><input id="line-debit" type="number" min="0" value={debit} onChange={e=>setDebit(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></Field>
    <Field label="Crédit (décaissement)" htmlFor="line-credit"><input id="line-credit" type="number" min="0" value={credit} onChange={e=>setCredit(+e.target.value)} className="w-full text-sm border-gray-300 rounded-md"/></Field></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}
function MatchModal({line,items,onClose}: Readonly<{line:Line;items:BankItem[];onClose:()=>void}>){
  const sorted=[...items].sort((a,b)=>Math.abs(Math.abs(a.net)-Math.abs(line.net))-Math.abs(Math.abs(b.net)-Math.abs(line.net)));
  return (<ModalShell title={'🔗 Rapprocher la ligne du '+new Date(line.transaction_date).toLocaleDateString('fr-FR')} onClose={onClose}>
    <div className="space-y-2 overflow-y-auto max-h-96">
      {sorted.length===0&&<p className="text-sm text-gray-500">Aucune écriture 521 non rapprochée.</p>}
      {sorted.map(i=>{const match=Math.abs(Math.abs(i.net)-Math.abs(line.net))<0.01;return(
        <div key={i.id} className={'p-3 rounded border flex justify-between items-center '+(match?'border-green-300 bg-green-50':'border-gray-200')}>
          <div>
            <p className="text-sm font-medium">{i.reference} · {new Date(i.entry_date).toLocaleDateString('fr-FR')}</p>
            <p className="text-xs text-gray-500">{i.description}</p>
            <p className="font-mono text-xs">{i.debit>0?'Débit '+formatMoney(i.debit):'Crédit '+formatMoney(i.credit)} {match&&<span className="font-semibold text-green-700">· montant correspondant</span>}</p>
          </div>
          <button type="button" onClick={()=>router.post(route('treasury.lines.match',line.id),{journal_item_id:i.id})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded">Choisir</button>
        </div>);})}
    </div>
  </ModalShell>);
}

function ModalShell({title,children,onClose}: Readonly<{title:string;children:React.ReactNode;onClose:()=>void}>){
  return (<div className="fixed inset-0 z-50 flex items-center justify-center p-4">
    <button type="button" className="absolute inset-0 bg-black/50" onClick={onClose} aria-label="Fermer la fenêtre" />
    <div className="relative z-10 bg-white rounded-lg shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
      <div className="sticky top-0 z-10 p-5 bg-white border-b"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
      <div className="p-5">{children}</div></div></div>);
}
function Field({label,children,htmlFor}: Readonly<{label:string;children:React.ReactNode;htmlFor?:string}>){
  return (<div><label htmlFor={htmlFor} className="block mb-1 text-sm font-medium text-gray-700">{label}</label>{children}</div>);
}
function ModalFooter({onClose}: Readonly<{onClose:()=>void}>){
  return (<div className="flex justify-end gap-3 pt-2 mt-4 border-t">
    <button type="button" onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
    <button type="submit" className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Enregistrer</button></div>);
}
function ConfirmDelete({label,onClose,onConfirm}: Readonly<{label:string;onClose:()=>void;onConfirm:()=>void}>){
  return (<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div className="w-full max-w-md bg-white rounded-lg shadow-2xl">
      <div className="p-6"><h2 className="text-lg font-bold text-red-700">🗑 Supprimer ?</h2>
      <p className="mt-2 text-sm text-gray-600">Confirmer la suppression de <strong>{label}</strong> ?</p></div>
      <div className="flex justify-end gap-3 p-5 border-t rounded-b-lg bg-gray-50">
        <button type="button" onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
        <button type="button" onClick={onConfirm} className="px-4 py-2 text-sm text-white bg-red-600 rounded-md">Supprimer</button></div>
    </div></div>);
}
