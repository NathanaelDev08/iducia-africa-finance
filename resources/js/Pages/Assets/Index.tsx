import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Asset { id:number; code:string; name:string; acquisition_date:string; acquisition_cost:number; residual_value:number; useful_life_months:number; monthly:number; status:string; accumulated:number; net_book_value:number; }
interface Dep { id:number; asset:{code:string;name:string}; period:string; amount:number; accumulated:number; net_book_value:number; status:string; is_posted:boolean; }
interface Props { assets:Asset[]; depreciations:Dep[]; stats:any; currentPeriod:string; initialTab:string; }
type TabKey='assets'|'depreciations';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index(p:Props){
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'assets');
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  return (
    <ErpLayout>
      <Head title="Immobilisations"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🏗️ Immobilisations</h1>
        <p className="text-sm text-gray-500 mt-1">Actifs fixes + amortissements automatiques (681 / 281)</p></div>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-indigo-500"><p className="text-xs text-gray-500 uppercase">Actifs</p><p className="text-xl font-bold">{p.stats.assets_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-blue-500"><p className="text-xs text-gray-500 uppercase">Valeur brute</p><p className="text-lg font-bold">{formatMoney(p.stats.gross_value)}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-red-500"><p className="text-xs text-gray-500 uppercase">Amortissements</p><p className="text-lg font-bold text-red-700">{formatMoney(p.stats.accumulated)}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-green-500"><p className="text-xs text-gray-500 uppercase">Valeur nette</p><p className="text-lg font-bold text-green-700">{formatMoney(p.stats.net_value)}</p></div>
        </div>
        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex">
          {([{key:'assets',label:'Actifs',icon:'🏗️'},{key:'depreciations',label:'Amortissements',icon:'📉'}] as {key:TabKey;label:string;icon:string}[]).map(t=>{const a=tab===t.key;return(
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>
        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='assets'&&<AssetsTab assets={p.assets}/>}
          {tab==='depreciations'&&<DepTab depreciations={p.depreciations} currentPeriod={p.currentPeriod}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

function AssetsTab({assets}:{assets:Asset[]}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Asset}>(null);
  const [del,setDel]=useState<Asset|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Immobilisation</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Désignation</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Acquisition</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Coût</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Durée (mois)</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Dotation/mois</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Amorti</th><th className="p-2 text-right text-xs text-gray-600 uppercase">VNC</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{assets.length===0?<tr><td colSpan={9} className="p-8 text-center text-gray-500">Aucune immobilisation.</td></tr>:assets.map(a=>(
        <tr key={a.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs">{a.code}</td><td className="p-2 font-medium">{a.name}</td>
          <td className="p-2 text-xs">{new Date(a.acquisition_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(a.acquisition_cost)}</td>
          <td className="p-2 text-right">{a.useful_life_months}</td>
          <td className="p-2 text-right font-mono">{formatMoney(a.monthly)}</td>
          <td className="p-2 text-right font-mono text-red-700">{formatMoney(a.accumulated)}</td>
          <td className="p-2 text-right font-mono font-semibold text-green-700">{formatMoney(a.net_book_value)}</td>
          <td className="p-2 text-right"><button onClick={()=>setModal({mode:'edit',item:a})} aria-label={`Modifier l'immobilisation ${a.name}`} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
          <button onClick={()=>setDel(a)} aria-label={`Supprimer l'immobilisation ${a.name}`} className="text-red-600 hover:underline text-xs">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal&&<AssetModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('assets.destroy',del.id))}/>}
  </div>);
}

function AssetModal({mode,item,onClose}:{mode:'create'|'edit';item?:Asset;onClose:()=>void}){
  const [form,setForm]=useState({code:item?.code||'',name:item?.name||'',acquisition_date:item?.acquisition_date||new Date().toISOString().slice(0,10),acquisition_cost:item?.acquisition_cost||0,residual_value:item?.residual_value||0,useful_life_months:item?.useful_life_months||60,account_asset:'',account_depreciation:'',account_expense:''});
  const [errors,setErrors]=useState<any>({});
  const submit=(e:React.FormEvent)=>{e.preventDefault();const o={onError:(er:any)=>setErrors(er),onSuccess:onClose};
    if(mode==='create')router.post(route('assets.store'),form,o);else router.put(route('assets.update',item!.id),form,o);};
  const set=(k:string,v:any)=>setForm(f=>({...f,[k]:v}));
  return (<ModalShell title={mode==='create'?'➕ Immobilisation':'✏️ Immobilisation'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div className="grid grid-cols-2 gap-3"><Field label="Code *" error={errors.code}><input value={form.code} onChange={e=>set('code',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    <Field label="Désignation *" error={errors.name}><input value={form.name} onChange={e=>set('name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Date acquisition *" error={errors.acquisition_date}><input type="date" value={form.acquisition_date} onChange={e=>set('acquisition_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    <Field label="Durée (mois) *" error={errors.useful_life_months}><input type="number" min="1" value={form.useful_life_months} onChange={e=>set('useful_life_months',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field></div>
    <div className="grid grid-cols-2 gap-3"><Field label="Coût acquisition *" error={errors.acquisition_cost}><input type="number" min="0" value={form.acquisition_cost} onChange={e=>set('acquisition_cost',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    <Field label="Valeur résiduelle"><input type="number" min="0" value={form.residual_value} onChange={e=>set('residual_value',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <div className="grid grid-cols-3 gap-3">
      <Field label="Compte actif (21x)"><input value={form.account_asset} onChange={e=>set('account_asset',e.target.value)} placeholder="211" className="w-full rounded-md border-gray-300 text-sm font-mono"/></Field>
      <Field label="Compte amort. (28x)"><input value={form.account_depreciation} onChange={e=>set('account_depreciation',e.target.value)} placeholder="281" className="w-full rounded-md border-gray-300 text-sm font-mono"/></Field>
      <Field label="Compte dotation (68x)"><input value={form.account_expense} onChange={e=>set('account_expense',e.target.value)} placeholder="681" className="w-full rounded-md border-gray-300 text-sm font-mono"/></Field></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function DepTab({depreciations,currentPeriod}:{depreciations:Dep[];currentPeriod:string}){
  const [period,setPeriod]=useState(currentPeriod);
  const [view,setView]=useState<ViewMode>('list');
  return (<div>
    <div className="flex gap-3 items-center mb-4">
      <input type="month" value={period} onChange={e=>setPeriod(e.target.value)} className="rounded-md border-gray-300 text-sm"/>
      <button onClick={()=>router.post(route('assets.generate'),{period})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">⚙️ Générer les dotations</button>
      <div className="ml-auto"><ViewSwitcher value={view} onChange={setView}/></div>
    </div>
    {view==='list'?(
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Période</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Actif</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Dotation</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Cumul</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">VNC</th><th className="p-2 text-center text-xs text-gray-600 uppercase">État</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Action</th></tr></thead>
      <tbody className="divide-y">{depreciations.length===0?<tr><td colSpan={7} className="p-8 text-center text-gray-500">Aucune dotation. Générez-les pour une période.</td></tr>:depreciations.map(d=>(
        <tr key={d.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs">{d.period}</td><td className="p-2 font-medium">{d.asset.name}</td>
          <td className="p-2 text-right font-mono">{formatMoney(d.amount)}</td>
          <td className="p-2 text-right font-mono text-red-700">{formatMoney(d.accumulated)}</td>
          <td className="p-2 text-right font-mono text-green-700">{formatMoney(d.net_book_value)}</td>
          <td className="p-2 text-center">{d.is_posted?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">📊 Comptabilisé</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}</td>
          <td className="p-2 text-right">{!d.is_posted&&<button onClick={()=>router.post(route('assets.post',d.id))} className="text-purple-600 hover:underline text-xs">📊 Comptabiliser</button>}</td>
        </tr>))}</tbody></table></div>
    ):(
    <KanbanBoard
      data={depreciations}
      rowKey={(d)=>d.id}
      groupBy={(d)=>d.is_posted?'posted':'draft'}
      columns={[
        {key:'draft',label:'Brouillon',colorClass:'bg-yellow-100 text-yellow-800'},
        {key:'posted',label:'Comptabilisé',colorClass:'bg-purple-100 text-purple-800'},
      ]}
      emptyMessage="Aucune dotation. Générez-les pour une période."
      renderCard={(d)=>(
        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="font-mono text-xs font-semibold">{d.period}</span>
            {d.is_posted?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">📊 Comptabilisé</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}
          </div>
          <div className="mt-1 font-medium text-gray-900">{d.asset.name}</div>
          <div className="mt-2 text-xs">Dotation <span className="font-mono font-semibold">{formatMoney(d.amount)}</span></div>
          <div className="mt-1 flex items-center justify-between text-xs">
            <span className="text-red-700">Cumul {formatMoney(d.accumulated)}</span>
            <span className="font-semibold text-green-700">VNC {formatMoney(d.net_book_value)}</span>
          </div>
          {!d.is_posted&&<div className="mt-3 flex border-t border-gray-100 pt-2 text-xs">
            <button onClick={()=>router.post(route('assets.post',d.id))} className="text-purple-600 hover:underline">📊 Comptabiliser</button>
          </div>}
        </div>
      )}
    />
    )}
  </div>);
}

function ModalShell({title,children,onClose}:{title:string;children:React.ReactNode;onClose:()=>void}){
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" onClick={e=>e.stopPropagation()}>
      <div className="p-5 border-b sticky top-0 bg-white z-10"><h2 className="text-lg font-bold text-gray-900">{title}</h2></div>
      <div className="p-5">{children}</div></div></div>);
}
function Field({label,error,children}:{label:string;error?:string;children:React.ReactNode}){
  return (<div><label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>{children}{error&&<p className="text-xs text-red-600 mt-1">{error}</p>}</div>);
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
