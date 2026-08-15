import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import ViewSwitcher, { ViewMode } from '@/Components/ViewSwitcher';
import KanbanBoard from '@/Components/KanbanBoard';

interface Item { id:number; code:string; name:string; category:string|null; unit:string; quantity_on_hand:number; unit_cost:number; value:number; reorder_threshold:number; below_threshold:boolean; is_active:boolean; }
interface Movement { id:number; item:{code:string;name:string}|null; type:string; quantity:number; reference:string|null; movement_date:string; }
interface Props { items:Item[]; movements:Movement[]; stats:any; initialTab:string; }
type TabKey = 'items' | 'movements';
const formatMoney = (v:number) => (v||0).toLocaleString('fr-FR') + ' FCFA';
const typeLabel: Record<string,string> = { in: 'Entrée', out: 'Sortie', adjustment: 'Ajustement' };

export default function Index(p:Props){
  const [tab,setTab] = useState<TabKey>((p.initialTab as TabKey) || 'items');
  useEffect(() => { const u = new URL(window.location.href); u.searchParams.set('tab', tab); window.history.replaceState({}, '', u.toString()); }, [tab]);

  return (
    <ErpLayout>
      <Head title="Stock & Inventaire" />
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">📦 Stock & Inventaire</h1>
        <p className="text-sm text-gray-500 mt-1">Articles, niveaux de stock et mouvements d'entrée/sortie.</p></div>

        <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-indigo-500"><p className="text-xs text-gray-500 uppercase">Articles</p><p className="text-xl font-bold">{p.stats.items_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-green-500"><p className="text-xs text-gray-500 uppercase">Valeur du stock</p><p className="text-lg font-bold text-green-700">{formatMoney(p.stats.total_value)}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-amber-500"><p className="text-xs text-gray-500 uppercase">Sous le seuil</p><p className="text-xl font-bold text-amber-700">{p.stats.below_threshold_count}</p></div>
        </div>


        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex">
          {([{key:'items',label:'Articles',icon:'📦'},{key:'movements',label:'Mouvements',icon:'🔄'}] as {key:TabKey;label:string;icon:string}[]).map(t => { const a = tab===t.key; return (
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>

        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='items' && <ItemsTab items={p.items}/>}
          {tab==='movements' && <MovementsTab movements={p.movements} items={p.items}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

function ItemsTab({items}:{items:Item[]}){
  const [modal,setModal] = useState<null|{mode:'create'}|{mode:'edit';item:Item}>(null);
  const [del,setDel] = useState<Item|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Article</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Désignation</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Catégorie</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Quantité</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Coût unitaire</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Valeur</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{items.length===0?<tr><td colSpan={8} className="p-8 text-center text-gray-500">Aucun article. Créez le premier.</td></tr>:items.map(i=>(
        <tr key={i.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs">{i.code}</td><td className="p-2 font-medium">{i.name}</td>
          <td className="p-2 text-xs text-gray-600">{i.category || '—'}</td>
          <td className="p-2 text-right font-mono">{i.quantity_on_hand} {i.unit}</td>
          <td className="p-2 text-right font-mono">{formatMoney(i.unit_cost)}</td>
          <td className="p-2 text-right font-mono font-semibold">{formatMoney(i.value)}</td>
          <td className="p-2 text-center">{i.below_threshold?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">⚠ Seuil bas</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">OK</span>}</td>
          <td className="p-2 text-right"><button onClick={()=>setModal({mode:'edit',item:i})} aria-label={`Modifier l'article ${i.name}`} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
          <button onClick={()=>setDel(i)} aria-label={`Supprimer l'article ${i.name}`} className="text-red-600 hover:underline text-xs">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal && <ItemModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del && <ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('inventory.items.destroy',del.id))}/>}
  </div>);
}

function ItemModal({mode,item,onClose}:{mode:'create'|'edit';item?:Item;onClose:()=>void}){
  const [form,setForm] = useState({
    code: item?.code || '', name: item?.name || '', category: item?.category || '',
    unit: item?.unit || 'unité', unit_cost: item?.unit_cost || 0, reorder_threshold: item?.reorder_threshold || 0,
  });
  const [errors,setErrors] = useState<any>({});
  const submit = (e:React.FormEvent) => {
    e.preventDefault();
    const o = { onError: (er:any) => setErrors(er), onSuccess: onClose };
    if (mode==='create') router.post(route('inventory.items.store'), form, o);
    else router.put(route('inventory.items.update', item!.id), form, o);
  };
  const set = (k:string, v:any) => setForm(f => ({...f, [k]: v}));
  return (<ModalShell title={mode==='create'?'➕ Article':'✏️ Article'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div className="grid grid-cols-2 gap-3">
      <Field label="Code *" error={errors.code}><input value={form.code} onChange={e=>set('code',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
      <Field label="Désignation *" error={errors.name}><input value={form.name} onChange={e=>set('name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    </div>
    <div className="grid grid-cols-2 gap-3">
      <Field label="Catégorie"><input value={form.category} onChange={e=>set('category',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      <Field label="Unité"><input value={form.unit} onChange={e=>set('unit',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    </div>
    <div className="grid grid-cols-2 gap-3">
      <Field label="Coût unitaire"><input type="number" min="0" step="0.01" value={form.unit_cost} onChange={e=>set('unit_cost',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      <Field label="Seuil de réappro."><input type="number" min="0" step="0.01" value={form.reorder_threshold} onChange={e=>set('reorder_threshold',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    </div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function MovementsTab({movements,items}:{movements:Movement[];items:Item[]}){
  const [modal,setModal] = useState(false);
  const [view,setView] = useState<ViewMode>('list');
  return (<div>
    <div className="flex justify-between items-center mb-4">
      <ViewSwitcher value={view} onChange={setView}/>
      <button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Mouvement</button>
    </div>
    {view==='list'?(
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Article</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Type</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Quantité</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th></tr></thead>
      <tbody className="divide-y">{movements.length===0?<tr><td colSpan={5} className="p-8 text-center text-gray-500">Aucun mouvement enregistré.</td></tr>:movements.map(m=>(
        <tr key={m.id} className="hover:bg-gray-50">
          <td className="p-2 text-xs">{new Date(m.movement_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 font-medium">{m.item ? `${m.item.code} · ${m.item.name}` : '—'}</td>
          <td className="p-2 text-center">
            <span className={'px-2 py-1 rounded-full text-xs font-semibold '+(m.type==='in'?'bg-green-100 text-green-800':m.type==='out'?'bg-red-100 text-red-800':'bg-blue-100 text-blue-800')}>
              {typeLabel[m.type] || m.type}
            </span>
          </td>
          <td className="p-2 text-right font-mono">{m.quantity}</td>
          <td className="p-2 text-xs text-gray-600">{m.reference || '—'}</td>
        </tr>))}</tbody></table></div>
    ):(
    <KanbanBoard
      data={movements}
      rowKey={(m)=>m.id}
      groupBy={(m)=>m.type}
      columns={[
        {key:'in',label:'Entrée',colorClass:'bg-green-100 text-green-800'},
        {key:'out',label:'Sortie',colorClass:'bg-red-100 text-red-800'},
        {key:'adjustment',label:'Ajustement',colorClass:'bg-blue-100 text-blue-800'},
      ]}
      emptyMessage="Aucun mouvement enregistré."
      renderCard={(m)=>(
        <div className="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
          <div className="flex items-center justify-between">
            <span className="text-xs text-gray-500">{new Date(m.movement_date).toLocaleDateString('fr-FR')}</span>
            <span className={'px-2 py-1 rounded-full text-xs font-semibold '+(m.type==='in'?'bg-green-100 text-green-800':m.type==='out'?'bg-red-100 text-red-800':'bg-blue-100 text-blue-800')}>
              {typeLabel[m.type] || m.type}
            </span>
          </div>
          <div className="mt-1 font-medium text-gray-900">{m.item ? `${m.item.code} · ${m.item.name}` : '—'}</div>
          <div className="mt-2 font-mono text-sm font-semibold">{m.quantity}</div>
          {m.reference && <div className="mt-1 text-xs text-gray-500">Réf. {m.reference}</div>}
        </div>
      )}
    />
    )}
    {modal && <MovementModal items={items} onClose={()=>setModal(false)}/>}
  </div>);
}

function MovementModal({items,onClose}:{items:Item[];onClose:()=>void}){
  const [form,setForm] = useState({
    stock_item_id: items[0]?.id || '', type: 'in', quantity: 0,
    reference: '', note: '', movement_date: new Date().toISOString().slice(0,10),
  });
  const [errors,setErrors] = useState<any>({});
  const submit = (e:React.FormEvent) => {
    e.preventDefault();
    router.post(route('inventory.movements.store'), form, { onError: (er:any) => setErrors(er), onSuccess: onClose });
  };
  const set = (k:string, v:any) => setForm(f => ({...f, [k]: v}));
  return (<ModalShell title="🔄 Nouveau mouvement" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Field label="Article *" error={errors.stock_item_id}>
      <select value={form.stock_item_id} onChange={e=>set('stock_item_id',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
        {items.map(i => <option key={i.id} value={i.id}>{i.code} · {i.name} (dispo : {i.quantity_on_hand} {i.unit})</option>)}
      </select>
    </Field>
    <div className="grid grid-cols-2 gap-3">
      <Field label="Type *" error={errors.type}>
        <select value={form.type} onChange={e=>set('type',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
          <option value="in">Entrée</option>
          <option value="out">Sortie</option>
          <option value="adjustment">Ajustement (nouveau niveau)</option>
        </select>
      </Field>
      <Field label="Quantité *" error={errors.quantity}><input type="number" min="0.01" step="0.01" value={form.quantity} onChange={e=>set('quantity',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    </div>
    <Field label="Date *" error={errors.movement_date}><input type="date" value={form.movement_date} onChange={e=>set('movement_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    <Field label="Référence"><input value={form.reference} onChange={e=>set('reference',e.target.value)} placeholder="ex. BL-2026-014" className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
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
