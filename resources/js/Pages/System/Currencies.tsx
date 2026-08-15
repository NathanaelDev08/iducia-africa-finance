import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';
interface Rate { id:number; currency_code:string; currency_name:string|null; rate_to_base:number; effective_from:string; is_active:boolean; }
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR');
export default function Currencies({rates}:{rates:Rate[]}){
  const [view,setView]=useState<ViewMode>('list');
  const [modal,setModal]=useState(false);
  const [code,setCode]=useState('EUR');const [name,setName]=useState('Euro');const [rate,setRate]=useState(655);const [from,setFrom]=useState(new Date().toISOString().slice(0,10));
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('currencies.store'),{currency_code:code,currency_name:name,rate_to_base:rate,effective_from:from},{onSuccess:()=>setModal(false)});};
  return (<ErpLayout><Head title="Devises"/>
    <div className="py-6"><div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="mb-6 flex justify-between items-center"><div><h1 className="text-2xl font-bold text-gray-900">💱 Multi-devises</h1><p className="text-sm text-gray-500 mt-1">Taux de change vers la devise de base (XOF)</p></div>
      <div className="flex items-center gap-3"><ViewSwitcher value={view} onChange={setView}/><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Taux</button></div></div>
      {view==='list' ? (
      <div className="bg-white rounded-lg shadow-sm overflow-hidden"><table className="w-full text-sm">
        <thead className="bg-gray-50 border-b"><tr><th className="p-3 text-left text-xs text-gray-600 uppercase">Devise</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th><th className="p-3 text-right text-xs text-gray-600 uppercase">1 unité → XOF</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Effectif le</th><th className="p-3 text-right text-xs text-gray-600 uppercase">Action</th></tr></thead>
        <tbody className="divide-y">{rates.length===0?<tr><td colSpan={5} className="p-8 text-center text-gray-500">Aucun taux. La devise de base est XOF.</td></tr>:rates.map(r=>(
          <tr key={r.id} className="hover:bg-gray-50"><td className="p-3 font-mono font-semibold">{r.currency_code}</td><td className="p-3">{r.currency_name||'—'}</td><td className="p-3 text-right font-mono">{formatMoney(r.rate_to_base)}</td><td className="p-3 text-xs">{new Date(r.effective_from).toLocaleDateString('fr-FR')}</td>
          <td className="p-3 text-right"><button onClick={()=>router.delete(route('currencies.destroy',r.id))} className="text-red-600 hover:underline text-xs">🗑</button></td></tr>))}</tbody></table></div>
      ) : (
      <KanbanBoard
        data={rates}
        rowKey={(r)=>r.id}
        groupBy={(r)=>(r.is_active?'active':'inactive')}
        columns={[
          { key:'active', label:'● Actifs', colorClass:'bg-green-100 text-green-800' },
          { key:'inactive', label:'● Inactifs', colorClass:'bg-red-100 text-red-800' },
        ]}
        emptyMessage="Aucun taux. La devise de base est XOF."
        renderCard={(r)=>(
          <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
            <div className="font-mono font-semibold text-gray-900">{r.currency_code}</div>
            <div className="text-xs text-gray-500">{r.currency_name||'—'}</div>
            <div className="mt-2 text-sm font-mono">{formatMoney(r.rate_to_base)} <span className="text-xs text-gray-500">/ XOF</span></div>
            <div className="mt-1 text-xs text-gray-500">Effectif le {new Date(r.effective_from).toLocaleDateString('fr-FR')}</div>
            <div className="mt-3 flex gap-3 border-t border-gray-100 pt-2 text-xs">
              <button onClick={()=>router.delete(route('currencies.destroy',r.id))} className="text-red-600 hover:underline">🗑 Supprimer</button>
            </div>
          </div>
        )}
      />
      )}
      {modal&&<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={()=>setModal(false)}><div className="bg-white rounded-lg shadow-2xl max-w-md w-full p-6" onClick={e=>e.stopPropagation()}>
        <h2 className="text-lg font-bold mb-4">➕ Nouveau taux</h2>
        <form onSubmit={submit} className="space-y-3">
          <div className="grid grid-cols-2 gap-3"><div><label className="block text-sm font-medium text-gray-700 mb-1">Code *</label><input value={code} onChange={e=>setCode(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
          <div><label className="block text-sm font-medium text-gray-700 mb-1">Nom</label><input value={name} onChange={e=>setName(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></div></div>
          <div className="grid grid-cols-2 gap-3"><div><label className="block text-sm font-medium text-gray-700 mb-1">1 unité → XOF *</label><input type="number" step="0.000001" value={rate} onChange={e=>setRate(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
          <div><label className="block text-sm font-medium text-gray-700 mb-1">Effectif le *</label><input type="date" value={from} onChange={e=>setFrom(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div></div>
          <div className="flex justify-end gap-3 pt-2"><button type="button" onClick={()=>setModal(false)} className="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md">Annuler</button><button type="submit" className="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md">Enregistrer</button></div>
        </form></div></div>}
    </div></div></ErpLayout>);
}
