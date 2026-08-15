import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Account { id:number; number:string; name:string; class_number:number; type:string; is_active:boolean; }
interface Journal { id:number; code:string; name:string; type:string; is_active:boolean; }
interface FiscalYear { id:number; name:string; start_date:string; end_date:string; status:string; }
interface Period { id:number; name:string; start_date:string; end_date:string; status:string; fiscal_year_name:string; }
interface Entry { id:number; entry_date:string; reference:string|null; description:string; journal:string|null; period:string|null; status:string; total_debit:number; total_credit:number; }
interface Props { accounts:Account[]; journals:Journal[]; fiscalYears:FiscalYear[]; periods:Period[]; entries:Entry[]; chartAccount:any; initialTab:string; }

type TabKey='ecritures'|'accounts'|'journals'|'fiscalYears'|'periods';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index({accounts=[],journals=[],fiscalYears=[],periods=[],entries=[],initialTab}:Props){
  const [tab,setTab]=useState<TabKey>((initialTab as TabKey)||'ecritures');
  const flash=(usePage().props as any).flash;
  const tabs=[
    {key:'ecritures' as TabKey,label:'Écritures',icon:'📝'},
    {key:'accounts' as TabKey,label:'Comptes',icon:'🔢'},
    {key:'journals' as TabKey,label:'Journaux',icon:'📔'},
    {key:'fiscalYears' as TabKey,label:'Exercices',icon:'📅'},
    {key:'periods' as TabKey,label:'Périodes',icon:'🗓️'},
  ];
  return (
    <ErpLayout>
      <Head title="Comptabilité"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">📒 Comptabilité</h1>
        <p className="text-sm text-gray-500 mt-1">Écritures, comptes, journaux, exercices et périodes</p></div>
        {flash?.success&&<div className="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">✓ {flash.success}</div>}
        {flash?.error&&<div className="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">✗ {flash.error}</div>}

        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex">
          {tabs.map(t=>{const a=tab===t.key;return(
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>

        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='ecritures'&&<EcrituresTab data={entries}/>}
          {tab==='accounts'&&<AccountsTab data={accounts}/>}
          {tab==='journals'&&<JournalsTab data={journals}/>}
          {tab==='fiscalYears'&&<FiscalYearsTab data={fiscalYears}/>}
          {tab==='periods'&&<PeriodsTab data={periods}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

function EcrituresTab({data}:{data:Entry[]}){
  const validateEntry=(e:Entry)=>{
    if(!confirm(`Valider l'écriture "${e.description}" ? Cette action est définitive.`))return;
    router.post(route('accounting.ecritures.validate',e.id));
  };

  const reverseEntry=(e:Entry)=>{
    const reason=prompt(`Motif de la contre-passation de "${e.description}" :`);
    if(!reason)return;
    router.post(route('accounting.ecritures.reverse',e.id),{reason});
  };

  return (<div>
    <div className="flex justify-end mb-4">
      <a href={route('accounting.ecritures.create')} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Nouvelle écriture</a>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Journal</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Libellé</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Débit</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Crédit</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.length===0?<tr><td colSpan={8} className="p-8 text-center text-gray-500">Aucune écriture. Créez-en une nouvelle.</td></tr>:data.map(e=>(
        <tr key={e.id} className="hover:bg-gray-50">
          <td className="p-2 text-xs">{new Date(e.entry_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 font-mono text-xs">{e.journal||'—'}</td>
          <td className="p-2 font-mono text-xs">{e.reference||'—'}</td>
          <td className="p-2">{e.description}</td>
          <td className="p-2 text-right font-mono">{formatMoney(e.total_debit)}</td>
          <td className="p-2 text-right font-mono">{formatMoney(e.total_credit)}</td>
          <td className="p-2 text-center">
            {e.status==='draft'&&<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}
            {e.status==='posted'&&<span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Validée</span>}
            {e.status==='cancelled'&&<span className="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Contre-passée</span>}
          </td>
          <td className="p-2 text-right whitespace-nowrap">
            {e.status==='draft'&&<button onClick={()=>validateEntry(e)} className="text-green-600 hover:underline text-xs mr-3">✓ Valider</button>}
            {e.status==='posted'&&<button onClick={()=>reverseEntry(e)} className="text-red-600 hover:underline text-xs">↩ Contre-passer</button>}
          </td>
        </tr>))}</tbody></table></div>
  </div>);
}

function AccountsTab({data}:{data:Account[]}){
  const [search,setSearch]=useState('');
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Account}>(null);
  const [del,setDel]=useState<Account|null>(null);
  const filtered=data.filter(a=>!search||a.number.includes(search)||a.name.toLowerCase().includes(search.toLowerCase()));
  return (<div>
    <div className="flex gap-3 mb-4 items-center">
      <input type="text" placeholder="Rechercher (n°, nom)..." value={search} onChange={e=>setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs"/>
      <button onClick={()=>setModal({mode:'create'})} className="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Compte</button>
    </div>
    <div className="overflow-x-auto max-h-[500px] overflow-y-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b sticky top-0"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">N°</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Libellé</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Classe</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Type</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{filtered.slice(0,200).map(a=>(<tr key={a.id} className="hover:bg-gray-50">
        <td className="p-2 font-mono text-xs">{a.number}</td><td className="p-2">{a.name}</td>
        <td className="p-2 text-center">{a.class_number}</td><td className="p-2 text-xs">{a.type}</td>
        <td className="p-2 text-right"><button onClick={()=>setModal({mode:'edit',item:a})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
        <button onClick={()=>setDel(a)} className="text-red-600 hover:underline text-xs">🗑</button></td>
      </tr>))}</tbody>
    </table></div>
    {modal&&<AccountModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.number+' '+del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('accounting.accounts.destroy',del.id))}/>}
  </div>);
}

function AccountModal({mode,item,onClose}:{mode:'create'|'edit';item?:Account;onClose:()=>void}){
  const [number,setNumber]=useState(item?.number||'');
  const [name,setName]=useState(item?.name||'');
  const [cls,setCls]=useState(item?.class_number||1);
  const [type,setType]=useState(item?.type||'asset');
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    const p={number,name,class_number:cls,type};
    if(mode==='create')router.post(route('accounting.accounts.store'),p,{onSuccess:onClose});
    else router.put(route('accounting.accounts.update',item!.id),p,{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Compte':'✏️ Compte'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="N° compte *" value={number} onChange={setNumber} required/>
    <Input label="Libellé *" value={name} onChange={setName} required/>
    <div className="grid grid-cols-2 gap-3">
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Classe</label>
        <select value={cls} onChange={e=>setCls(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm">{[1,2,3,4,5,6,7,8,9].map(n=><option key={n} value={n}>{n}</option>)}</select></div>
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select value={type} onChange={e=>setType(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
          <option value="asset">Actif</option><option value="liability">Passif</option><option value="equity">Capitaux</option>
          <option value="revenue">Produit</option><option value="expense">Charge</option><option value="bank">Banque</option>
          <option value="cash">Caisse</option><option value="customer">Client</option><option value="supplier">Fournisseur</option>
        </select></div>
    </div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function JournalsTab({data}:{data:Journal[]}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Journal}>(null);
  const [del,setDel]=useState<Journal|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Journal</button></div>
    <table className="w-full text-sm"><thead className="bg-gray-50 border-b"><tr>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Type</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.map(j=>(<tr key={j.id} className="hover:bg-gray-50">
        <td className="p-2 font-mono text-xs">{j.code}</td><td className="p-2">{j.name}</td><td className="p-2 text-xs">{j.type}</td>
        <td className="p-2 text-right"><button onClick={()=>setModal({mode:'edit',item:j})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
        <button onClick={()=>setDel(j)} className="text-red-600 hover:underline text-xs">🗑</button></td>
      </tr>))}</tbody></table>
    {modal&&<JournalModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('accounting.journals.destroy',del.id))}/>}
  </div>);
}

function JournalModal({mode,item,onClose}:{mode:'create'|'edit';item?:Journal;onClose:()=>void}){
  const [code,setCode]=useState(item?.code||'');
  const [name,setName]=useState(item?.name||'');
  const [type,setType]=useState(item?.type||'general');
  const submit=(e:React.FormEvent)=>{e.preventDefault();const p={code,name,type};
    if(mode==='create')router.post(route('accounting.journals.store'),p,{onSuccess:onClose});
    else router.put(route('accounting.journals.update',item!.id),p,{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Journal':'️ Journal'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Code *" value={code} onChange={setCode} required/>
    <Input label="Nom *" value={name} onChange={setName} required/>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
      <select value={type} onChange={e=>setType(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
        <option value="general">Général</option><option value="sale">Ventes</option><option value="purchase">Achats</option>
        <option value="bank">Banque</option><option value="cash">Caisse</option><option value="payroll">Paie</option><option value="od">OD</option>
      </select></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function FiscalYearsTab({data}:{data:FiscalYear[]}){
  const [modal,setModal]=useState(false);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Exercice</button></div>
    <table className="w-full text-sm"><thead className="bg-gray-50 border-b"><tr>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Du</th>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Au</th><th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th>
      <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.map(f=>(<tr key={f.id} className="hover:bg-gray-50">
        <td className="p-2 font-medium">{f.name}</td><td className="p-2 text-xs">{new Date(f.start_date).toLocaleDateString('fr-FR')}</td>
        <td className="p-2 text-xs">{new Date(f.end_date).toLocaleDateString('fr-FR')}</td>
        <td className="p-2 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+(f.status==='open'?'bg-green-100 text-green-800':'bg-red-100 text-red-800')}>{f.status==='open'?'Ouvert':'Clôturé'}</span></td>
        <td className="p-2 text-right">{f.status==='open'&&<button onClick={()=>router.post(route('accounting.fiscal-years.close',f.id))} className="text-red-600 hover:underline text-xs">🔒 Clôturer</button>}</td>
      </tr>))}</tbody></table>
    {modal&&<FiscalYearModal onClose={()=>setModal(false)}/>}
  </div>);
}

function FiscalYearModal({onClose}:{onClose:()=>void}){
  const [name,setName]=useState('');
  const [start,setStart]=useState(new Date().getFullYear()+'-01-01');
  const [end,setEnd]=useState(new Date().getFullYear()+'-12-31');
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    router.post(route('accounting.fiscal-years.store'),{name:name||('Exercice '+start.slice(0,4)),start_date:start,end_date:end},{onSuccess:onClose});};
  return (<ModalShell title="➕ Exercice fiscal" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Nom" value={name} onChange={setName}/>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Début *</label><input type="date" value={start} onChange={e=>setStart(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Fin *</label><input type="date" value={end} onChange={e=>setEnd(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    <p className="text-xs text-gray-500">ℹ️ Les 12 périodes mensuelles seront créées automatiquement.</p>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function PeriodsTab({data}:{data:Period[]}){
  return (<div className="overflow-x-auto"><table className="w-full text-sm">
    <thead className="bg-gray-50 border-b"><tr>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Période</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Exercice</th>
      <th className="p-2 text-left text-xs text-gray-600 uppercase">Du</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Au</th>
      <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
    <tbody className="divide-y">{data.map(p=>(<tr key={p.id} className="hover:bg-gray-50">
      <td className="p-2 font-medium">{p.name}</td><td className="p-2 text-xs">{p.fiscal_year_name}</td>
      <td className="p-2 text-xs">{new Date(p.start_date).toLocaleDateString('fr-FR')}</td><td className="p-2 text-xs">{new Date(p.end_date).toLocaleDateString('fr-FR')}</td>
      <td className="p-2 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+(p.status==='open'?'bg-green-100 text-green-800':'bg-red-100 text-red-800')}>{p.status==='open'?'Ouverte':'Clôturée'}</span></td>
      <td className="p-2 text-right">{p.status==='open'
        ?<button onClick={()=>router.post(route('accounting.periods.close',p.id))} className="text-red-600 hover:underline text-xs">🔒 Clôturer</button>
        :<button onClick={()=>router.post(route('accounting.periods.reopen',p.id))} className="text-green-600 hover:underline text-xs">🔓 Rouvrir</button>}</td>
    </tr>))}</tbody></table></div>);
}

/* ===== GÉNÉRIQUES ===== */
function ModalShell({title,children,onClose}:{title:string;children:React.ReactNode;onClose:()=>void}){
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full" onClick={e=>e.stopPropagation()}>
      <div className="p-5 border-b"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
      <div className="p-5">{children}</div></div></div>);
}
function Input({label,value,onChange,required}:{label:string;value:string;onChange:(v:string)=>void;required?:boolean}){
  return (<div><label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
    <input type="text" value={value} onChange={e=>onChange(e.target.value)} required={required} className="w-full rounded-md border-gray-300 text-sm"/></div>);
}
function ModalFooter({onClose}:{onClose:()=>void}){
  return (<div className="flex justify-end gap-3 pt-2">
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
