import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Statement { id:number; account:string; period_start:string; period_end:string; opening_balance:number; closing_balance:number; status:string; lines_count:number; matched_count:number; }
interface Line { id:number; transaction_date:string; reference:string|null; description:string|null; debit:number; credit:number; net:number; status:string; is_matched:boolean; }
interface BankItem { id:number; entry_date:string; reference:string; description:string; debit:number; credit:number; net:number; }
interface Props { statements:Statement[]; selected:Statement|null; lines:Line[]; unmatchedItems:BankItem[]; bankAccounts:{id:number;number:string;name:string}[]; initialTab:string; }
type TabKey='statements'|'reconciliation';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index(p:Props){
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'statements');
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  const flash=(usePage().props as any).flash;
  return (
    <ErpLayout>
      <Head title="Trésorerie"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🏦 Trésorerie</h1>
        <p className="text-sm text-gray-500 mt-1">Rapprochement bancaire : relevé ↔ écritures 521</p></div>
        {flash?.success&&<div className="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
        {flash?.error&&<div className="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}
        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex">
          {([{key:'statements',label:'Relevés',icon:'🏦'},{key:'reconciliation',label:'Rapprochement',icon:'🔗'}] as {key:TabKey;label:string;icon:string}[]).map(t=>{const a=tab===t.key;return(
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>
        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='statements'&&<StatementsTab statements={p.statements} bankAccounts={p.bankAccounts}/>}
          {tab==='reconciliation'&&<ReconciliationTab selected={p.selected} lines={p.lines} unmatchedItems={p.unmatchedItems} statements={p.statements}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

function StatementsTab({statements,bankAccounts}:{statements:Statement[];bankAccounts:any[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Statement|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Relevé</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Compte</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Du</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Au</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Solde ouv.</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Solde clô.</th><th className="p-2 text-center text-xs text-gray-600 uppercase">Lignes</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Rapprochées</th><th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{statements.length===0?<tr><td colSpan={9} className="p-8 text-center text-gray-500">Aucun relevé.</td></tr>:statements.map(s=>(
        <tr key={s.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs">{s.account}</td>
          <td className="p-2 text-xs">{new Date(s.period_start).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-xs">{new Date(s.period_end).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(s.opening_balance)}</td>
          <td className="p-2 text-right font-mono">{formatMoney(s.closing_balance)}</td>
          <td className="p-2 text-center">{s.lines_count}</td>
          <td className="p-2 text-center text-green-700 font-semibold">{s.matched_count}</td>
          <td className="p-2 text-center">{s.status==='reconciled'?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Rapproché</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}</td>
          <td className="p-2 text-right">
            <a href={route('treasury.index',{tab:'reconciliation',statement:s.id})} className="text-indigo-600 hover:underline text-xs mr-3">🔗 Rapprocher</a>
            <button onClick={()=>setDel(s)} className="text-red-600 hover:underline text-xs">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal&&<StatementModal bankAccounts={bankAccounts} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={'relevé '+del.account} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('treasury.destroy',del.id))}/>}
  </div>);
}
function StatementModal({bankAccounts,onClose}:{bankAccounts:any[];onClose:()=>void}){
  const [accountId,setAccountId]=useState('');
  const [start,setStart]=useState(new Date().toISOString().slice(0,8)+'01');
  const [end,setEnd]=useState(new Date().toISOString().slice(0,10));
  const [opening,setOpening]=useState(0);const [closing,setClosing]=useState(0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('treasury.store'),{account_id:accountId||null,period_start:start,period_end:end,opening_balance:opening,closing_balance:closing},{onSuccess:onClose});};
  return (<ModalShell title="➕ Relevé bancaire" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Compte banque</label>
      <select value={accountId} onChange={e=>setAccountId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{bankAccounts.map((a:any)=><option key={a.id} value={a.id}>{a.number} - {a.name}</option>)}</select></div>
    <div className="grid grid-cols-2 gap-3">
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Début *</label><input type="date" value={start} onChange={e=>setStart(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Fin *</label><input type="date" value={end} onChange={e=>setEnd(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div></div>
    <div className="grid grid-cols-2 gap-3">
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Solde ouverture</label><input type="number" value={opening} onChange={e=>setOpening(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></div>
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Solde clôture</label><input type="number" value={closing} onChange={e=>setClosing(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></div></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function ReconciliationTab({selected,lines,unmatchedItems,statements}:{selected:Statement|null;lines:Line[];unmatchedItems:BankItem[];statements:Statement[]}){
  const [lineModal,setLineModal]=useState(false);
  const [matchFor,setMatchFor]=useState<Line|null>(null);
  if(!selected) return <div className="p-8 text-center text-gray-500">Créez d'abord un relevé dans l'onglet « Relevés ».</div>;
  const totalDebit=lines.reduce((s,l)=>s+l.debit,0);
  const totalCredit=lines.reduce((s,l)=>s+l.credit,0);
  const unmatched=lines.filter(l=>!l.is_matched).length;
  return (<div>
    <div className="flex flex-wrap items-center gap-3 mb-4">
      <select value={selected.id} onChange={e=>router.get(route('treasury.index'),{tab:'reconciliation',statement:e.target.value},{preserveState:false})} className="rounded-md border-gray-300 text-sm">
        {statements.map(s=><option key={s.id} value={s.id}>{s.account} · {new Date(s.period_start).toLocaleDateString('fr-FR')} → {new Date(s.period_end).toLocaleDateString('fr-FR')}</option>)}
      </select>
      <button onClick={()=>setLineModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ligne</button>
      <button onClick={()=>router.post(route('treasury.reconcile',selected.id))} disabled={unmatched>0} className="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-sm py-2 px-4 rounded-md">✅ Rapprocher le relevé</button>
      <div className="ml-auto text-sm text-gray-600">Débit {formatMoney(totalDebit)} · Crédit {formatMoney(totalCredit)} · <span className={unmatched>0?'text-yellow-700 font-semibold':'text-green-700 font-semibold'}>{unmatched} non rapprochée(s)</span></div>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Réf</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Description</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Débit</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Crédit</th><th className="p-2 text-center text-xs text-gray-600 uppercase">État</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{lines.length===0?<tr><td colSpan={7} className="p-8 text-center text-gray-500">Aucune ligne. Ajoutez-les ou importez le relevé.</td></tr>:lines.map(l=>(
        <tr key={l.id} className="hover:bg-gray-50">
          <td className="p-2 text-xs">{new Date(l.transaction_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 font-mono text-xs">{l.reference||'—'}</td><td className="p-2 text-xs">{l.description||'—'}</td>
          <td className="p-2 text-right font-mono">{l.debit>0?formatMoney(l.debit):'—'}</td>
          <td className="p-2 text-right font-mono">{l.credit>0?formatMoney(l.credit):'—'}</td>
          <td className="p-2 text-center">{l.is_matched?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">🔗 Rapprochée</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">À rapprocher</span>}</td>
          <td className="p-2 text-right">
            {l.is_matched
              ?<button onClick={()=>router.post(route('treasury.lines.unmatch',l.id))} className="text-gray-600 hover:underline text-xs mr-2">Délier</button>
              :<button onClick={()=>setMatchFor(l)} className="text-indigo-600 hover:underline text-xs mr-2">🔗 Rapprocher</button>}
            <button onClick={()=>router.delete(route('treasury.lines.destroy',l.id))} className="text-red-600 hover:underline text-xs">🗑</button>
          </td>
        </tr>))}</tbody></table></div>
    {lineModal&&<LineModal statementId={selected.id} onClose={()=>setLineModal(false)}/>}
    {matchFor&&<MatchModal line={matchFor} items={unmatchedItems} onClose={()=>setMatchFor(null)}/>}
  </div>);
}
function LineModal({statementId,onClose}:{statementId:number;onClose:()=>void}){
  const [date,setDate]=useState(new Date().toISOString().slice(0,10));
  const [reference,setReference]=useState('');const [description,setDescription]=useState('');
  const [debit,setDebit]=useState(0);const [credit,setCredit]=useState(0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('treasury.lines.store'),{bank_statement_id:statementId,transaction_date:date,reference,description,debit,credit},{onSuccess:onClose});};
  return (<ModalShell title="➕ Ligne de relevé" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Date *</label><input type="date" value={date} onChange={e=>setDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Référence"><input value={reference} onChange={e=>setReference(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <Field label="Description"><input value={description} onChange={e=>setDescription(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Débit (encaissement)"><input type="number" min="0" value={debit} onChange={e=>setDebit(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <Field label="Crédit (décaissement)"><input type="number" min="0" value={credit} onChange={e=>setCredit(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}
function MatchModal({line,items,onClose}:{line:Line;items:BankItem[];onClose:()=>void}){
  const sorted=[...items].sort((a,b)=>Math.abs(Math.abs(a.net)-Math.abs(line.net))-Math.abs(Math.abs(b.net)-Math.abs(line.net)));
  return (<ModalShell title={'🔗 Rapprocher la ligne du '+new Date(line.transaction_date).toLocaleDateString('fr-FR')} onClose={onClose}>
    <div className="space-y-2 max-h-96 overflow-y-auto">
      {sorted.length===0&&<p className="text-sm text-gray-500">Aucune écriture 521 non rapprochée.</p>}
      {sorted.map(i=>{const match=Math.abs(Math.abs(i.net)-Math.abs(line.net))<0.01;return(
        <div key={i.id} className={'p-3 rounded border flex justify-between items-center '+(match?'border-green-300 bg-green-50':'border-gray-200')}>
          <div>
            <p className="text-sm font-medium">{i.reference} · {new Date(i.entry_date).toLocaleDateString('fr-FR')}</p>
            <p className="text-xs text-gray-500">{i.description}</p>
            <p className="text-xs font-mono">{i.debit>0?'Débit '+formatMoney(i.debit):'Crédit '+formatMoney(i.credit)} {match&&<span className="text-green-700 font-semibold">· montant correspondant</span>}</p>
          </div>
          <button onClick={()=>router.post(route('treasury.lines.match',line.id),{journal_item_id:i.id})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded">Choisir</button>
        </div>);})}
    </div>
  </ModalShell>);
}

function ModalShell({title,children,onClose}:{title:string;children:React.ReactNode;onClose:()=>void}){
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" onClick={e=>e.stopPropagation()}>
      <div className="p-5 border-b sticky top-0 bg-white z-10"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
      <div className="p-5">{children}</div></div></div>);
}
function Field({label,children}:{label:string;children:React.ReactNode}){
  return (<div><label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>{children}</div>);
}
function ModalFooter({onClose}:{onClose:()=>void}){
  return (<div className="flex justify-end gap-3 pt-2 mt-4 border-t">
    <button type="button" onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
    <button type="submit" className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Enregistrer</button></div>);
}
function ConfirmDelete({label,onClose,onConfirm}:{label:string;onClose:()=>void;onConfirm:()=>void}){
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full">
      <div className="p-6"><h2 className="text-lg font-bold text-red-700">🗑 Supprimer ?</h2>
      <p className="mt-2 text-sm text-gray-600">Confirmer la suppression de <strong>{label}</strong> ?</p></div>
      <div className="p-5 border-t bg-gray-50 flex justify-end gap-3 rounded-b-lg">
        <button onClick={onClose} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button>
        <button onClick={onConfirm} className="px-4 py-2 text-sm text-white bg-red-600 rounded-md">Supprimer</button></div>
    </div></div>);
}
