import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Supplier { id:number; code:string; name:string; contact_name:string|null; email:string|null; phone:string|null; tax_number:string|null; account_number:string|null; is_active:boolean; }
interface Order { id:number; reference:string; supplier:{id:number;name:string}; order_date:string; expected_date:string|null; status:string; total_ttc:number; }
interface Invoice { id:number; reference:string; supplier_invoice_number:string|null; supplier:{id:number;name:string}; invoice_date:string; due_date:string|null; status:string; total_ttc:number; amount_paid:number; remaining:number; is_posted:boolean; }
interface Payment { id:number; reference:string; supplier:{id:number;name:string}; invoice_reference:string; payment_date:string; payment_method:string; amount:number; is_posted:boolean; }
interface Account { id:number; number:string; name:string; }
interface Props { suppliers:Supplier[]; orders:Order[]; invoices:Invoice[]; payments:Payment[]; expenseAccounts:Account[]; stats:any; initialTab:string; }

type TabKey='suppliers'|'orders'|'invoices'|'payments';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

const ORDER_STATUS: Record<string,{label:string;color:string}> = {
  draft:{label:'Brouillon',color:'bg-gray-100 text-gray-800'},
  sent:{label:'Envoyé',color:'bg-blue-100 text-blue-800'},
  received:{label:'Reçu',color:'bg-green-100 text-green-800'},
  cancelled:{label:'Annulé',color:'bg-red-100 text-red-800'},
};

const INVOICE_STATUS: Record<string,{label:string;color:string}> = {
  draft:{label:'Brouillon',color:'bg-gray-100 text-gray-800'},
  validated:{label:'Validée',color:'bg-blue-100 text-blue-800'},
  paid:{label:'Payée',color:'bg-green-100 text-green-800'},
  cancelled:{label:'Annulée',color:'bg-red-100 text-red-800'},
};

export default function Index(p:Props){
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'suppliers');
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  const tabs=[
    {key:'suppliers' as TabKey,label:'Fournisseurs',icon:'🏭'},
    {key:'orders' as TabKey,label:'Bons de commande',icon:'📋'},
    {key:'invoices' as TabKey,label:'Factures',icon:'📄'},
    {key:'payments' as TabKey,label:'Paiements',icon:'💸'},
  ];
  return (
    <ErpLayout>
      <Head title="Achats"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🛒 Module Achats</h1>
        <p className="text-sm text-gray-500 mt-1">Fournisseurs, Bons de commande, Factures, Paiements avec comptabilisation automatique</p></div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-indigo-500"><p className="text-xs text-gray-500 uppercase">Fournisseurs</p><p className="text-xl font-bold">{p.stats.suppliers_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-blue-500"><p className="text-xs text-gray-500 uppercase">Commandes</p><p className="text-xl font-bold">{p.stats.orders_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-purple-500"><p className="text-xs text-gray-500 uppercase">Factures</p><p className="text-xl font-bold">{p.stats.invoices_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-red-500"><p className="text-xs text-gray-500 uppercase">Impayés</p><p className="text-lg font-bold text-red-700">{formatMoney(p.stats.unpaid_total)}</p></div>
        </div>


        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex">
          {tabs.map(t=>{const a=tab===t.key;return(
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>

        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='suppliers'&&<SuppliersTab suppliers={p.suppliers}/>}
          {tab==='orders'&&<OrdersTab orders={p.orders} suppliers={p.suppliers}/>}
          {tab==='invoices'&&<InvoicesTab invoices={p.invoices} suppliers={p.suppliers} expenseAccounts={p.expenseAccounts}/>}
          {tab==='payments'&&<PaymentsTab payments={p.payments} suppliers={p.suppliers} invoices={p.invoices}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

/* ===== ONGLET FOURNISSEURS ===== */
function SuppliersTab({suppliers}:{suppliers:Supplier[]}){
  const [search,setSearch]=useState('');
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Supplier}>(null);
  const [del,setDel]=useState<Supplier|null>(null);
  const filtered=suppliers.filter(s=>!search||s.name.toLowerCase().includes(search.toLowerCase())||s.code.toLowerCase().includes(search.toLowerCase()));
  return (<div>
    <div className="flex gap-3 mb-4 items-center">
      <input type="text" placeholder="Rechercher..." value={search} onChange={e=>setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs"/>
      <button onClick={()=>setModal({mode:'create'})} className="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Fournisseur</button>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Contact</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Email/Tel</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">N° fiscal</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Compte</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{filtered.map(s=>(<tr key={s.id} className="hover:bg-gray-50">
        <td className="p-2 font-mono text-xs">{s.code}</td><td className="p-2 font-medium">{s.name}</td>
        <td className="p-2 text-xs">{s.contact_name||'—'}</td>
        <td className="p-2 text-xs">{s.email||s.phone||'—'}</td>
        <td className="p-2 text-xs">{s.tax_number||'—'}</td>
        <td className="p-2 text-xs font-mono">{s.account_number||'—'}</td>
        <td className="p-2 text-right">
          <button onClick={()=>setModal({mode:'edit',item:s})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
          <button onClick={()=>setDel(s)} className="text-red-600 hover:underline text-xs">🗑</button></td>
      </tr>))}</tbody></table></div>
    {modal&&<SupplierModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('purchasing.suppliers.destroy',del.id))}/>}
  </div>);
}

function SupplierModal({mode,item,onClose}:{mode:'create'|'edit';item?:Supplier;onClose:()=>void}){
  const [form,setForm]=useState({code:item?.code||'',name:item?.name||'',contact_name:item?.contact_name||'',email:item?.email||'',phone:item?.phone||'',address:'',tax_number:item?.tax_number||'',account_number:item?.account_number||''});
  const [errors,setErrors]=useState<any>({});
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    const opts={onError:(er:any)=>setErrors(er),onSuccess:onClose};
    if(mode==='create')router.post(route('purchasing.suppliers.store'),form,opts);
    else router.put(route('purchasing.suppliers.update',item!.id),form,opts);};
  const set=(k:string,v:any)=>setForm(f=>({...f,[k]:v}));
  return (<ModalShell title={mode==='create'?'➕ Fournisseur':'✏️ Fournisseur'} onClose={onClose}>
    <form onSubmit={submit} className="space-y-3">
      <div className="grid grid-cols-2 gap-3">
        <Field label="Code *" error={errors.code}><input value={form.code} onChange={e=>set('code',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
        <Field label="Nom *" error={errors.name}><input value={form.name} onChange={e=>set('name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
      </div>
      <Field label="Contact"><input value={form.contact_name} onChange={e=>set('contact_name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      <div className="grid grid-cols-2 gap-3">
        <Field label="Email" error={errors.email}><input type="email" value={form.email} onChange={e=>set('email',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
        <Field label="Téléphone"><input value={form.phone} onChange={e=>set('phone',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      </div>
      <div className="grid grid-cols-2 gap-3">
        <Field label="N° fiscal (NCC)"><input value={form.tax_number} onChange={e=>set('tax_number',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
        <Field label="Compte 401"><input value={form.account_number} onChange={e=>set('account_number',e.target.value)} placeholder="401XXXXX" className="w-full rounded-md border-gray-300 text-sm font-mono"/></Field>
      </div>
      <ModalFooter onClose={onClose}/>
    </form></ModalShell>);
}

/* ===== ONGLET BONS DE COMMANDE ===== */
function OrdersTab({orders,suppliers}:{orders:Order[];suppliers:Supplier[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Order|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Bon de commande</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Fournisseur</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Total TTC</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{orders.length===0?<tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucun bon de commande.</td></tr>:orders.map(o=>{const cfg=ORDER_STATUS[o.status]||ORDER_STATUS.draft;return(
        <tr key={o.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{o.reference}</td>
          <td className="p-2 font-medium">{o.supplier.name}</td>
          <td className="p-2 text-xs">{new Date(o.order_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(o.total_ttc)}</td>
          <td className="p-2 text-center">
            <select value={o.status} onChange={e=>router.put(route('purchasing.orders.status',o.id),{status:e.target.value})} className="rounded-md border-gray-300 text-xs py-1">
              {Object.entries(ORDER_STATUS).map(([k,v])=><option key={k} value={k}>{v.label}</option>)}
            </select>
          </td>
          <td className="p-2 text-right">{o.status==='draft'&&<button onClick={()=>setDel(o)} className="text-red-600 hover:underline text-xs">🗑</button>}</td>
        </tr>);})}</tbody></table></div>
    {modal&&<OrderModal suppliers={suppliers} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('purchasing.orders.destroy',del.id))}/>}
  </div>);
}

function OrderModal({suppliers,onClose}:{suppliers:Supplier[];onClose:()=>void}){
  const [supplierId,setSupplierId]=useState('');
  const [expected,setExpected]=useState('');
  const [items,setItems]=useState([{description:'',quantity:1,unit_price:0,tax_rate:18}]);
  const [errors,setErrors]=useState<any>({});
  const addItem=()=>setItems([...items,{description:'',quantity:1,unit_price:0,tax_rate:18}]);
  const removeItem=(i:number)=>setItems(items.filter((_,idx)=>idx!==i));
  const updateItem=(i:number,k:string,v:any)=>setItems(items.map((it,idx)=>idx===i?{...it,[k]:v}:it));
  const total=items.reduce((s,it)=>s+(it.quantity*it.unit_price*(1+it.tax_rate/100)),0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    router.post(route('purchasing.orders.store'),{supplier_id:supplierId,expected_date:expected||null,items},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Bon de commande" onClose={onClose} size="lg">
    <form onSubmit={submit} className="space-y-3">
      <div className="grid grid-cols-2 gap-3">
        <Field label="Fournisseur *" error={errors.supplier_id}>
          <select value={supplierId} onChange={e=>setSupplierId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
            <option value="">—</option>{suppliers.map(s=><option key={s.id} value={s.id}>{s.code} - {s.name}</option>)}
          </select></Field>
        <Field label="Date prévue"><input type="date" value={expected} onChange={e=>setExpected(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      </div>
      <div>
        <div className="flex justify-between items-center mb-2">
          <label className="block text-sm font-medium text-gray-700">Lignes *</label>
          <button type="button" onClick={addItem} className="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">+ Ligne</button>
        </div>
        <div className="space-y-2 max-h-64 overflow-y-auto">
          {items.map((it,i)=>(
            <div key={i} className="grid grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded">
              <input value={it.description} onChange={e=>updateItem(i,'description',e.target.value)} placeholder="Description" className="col-span-5 rounded-md border-gray-300 text-sm" required/>
              <input type="number" step="0.01" min="0" value={it.quantity} onChange={e=>updateItem(i,'quantity',+e.target.value)} placeholder="Qté" className="col-span-2 rounded-md border-gray-300 text-sm" required/>
              <input type="number" step="0.01" min="0" value={it.unit_price} onChange={e=>updateItem(i,'unit_price',+e.target.value)} placeholder="PU" className="col-span-2 rounded-md border-gray-300 text-sm" required/>
              <select value={it.tax_rate} onChange={e=>updateItem(i,'tax_rate',+e.target.value)} className="col-span-2 rounded-md border-gray-300 text-sm">
                <option value="0">0%</option><option value="9">9%</option><option value="18">18%</option>
              </select>
              <button type="button" onClick={()=>removeItem(i)} className="col-span-1 text-red-600 hover:text-red-800">✕</button>
            </div>))}
        </div>
      </div>
      <div className="text-right font-bold text-lg border-t pt-2">Total TTC : {formatMoney(total)}</div>
      <ModalFooter onClose={onClose}/>
    </form></ModalShell>);
}

/* ===== ONGLET FACTURES ===== */
function InvoicesTab({invoices,suppliers,expenseAccounts}:{invoices:Invoice[];suppliers:Supplier[];expenseAccounts:Account[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Invoice|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Facture</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">N° four.</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Fournisseur</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Total TTC</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Payé</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Reste</th><th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{invoices.length===0?<tr><td colSpan={9} className="p-8 text-center text-gray-500">Aucune facture.</td></tr>:invoices.map(i=>{const cfg=INVOICE_STATUS[i.status]||INVOICE_STATUS.draft;return(
        <tr key={i.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{i.reference}</td>
          <td className="p-2 text-xs">{i.supplier_invoice_number||'—'}</td>
          <td className="p-2 font-medium">{i.supplier.name}</td>
          <td className="p-2 text-xs">{new Date(i.invoice_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(i.total_ttc)}</td>
          <td className="p-2 text-right font-mono text-green-700">{formatMoney(i.amount_paid)}</td>
          <td className="p-2 text-right font-mono text-red-700 font-semibold">{formatMoney(i.remaining)}</td>
          <td className="p-2 text-center">
            <span className={'px-2 py-1 rounded-full text-xs font-semibold '+cfg.color}>{cfg.label}</span>
            {i.is_posted&&<span className="ml-1 text-xs text-purple-700">📊</span>}
          </td>
          <td className="p-2 text-right">
            {!i.is_posted&&i.status==='draft'&&<button onClick={()=>router.post(route('purchasing.invoices.post',i.id))} className="text-purple-600 hover:underline text-xs mr-2" title="Comptabiliser">📊</button>}
            {!i.is_posted&&i.status==='draft'&&<button onClick={()=>setDel(i)} className="text-red-600 hover:underline text-xs">🗑</button>}
          </td>
        </tr>);})}</tbody></table></div>
    {modal&&<InvoiceModal suppliers={suppliers} expenseAccounts={expenseAccounts} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('purchasing.invoices.destroy',del.id))}/>}
  </div>);
}

function InvoiceModal({suppliers,expenseAccounts,onClose}:{suppliers:Supplier[];expenseAccounts:Account[];onClose:()=>void}){
  const [supplierId,setSupplierId]=useState('');
  const [supplierInvNum,setSupplierInvNum]=useState('');
  const [invoiceDate,setInvoiceDate]=useState(new Date().toISOString().slice(0,10));
  const [dueDate,setDueDate]=useState('');
  const [items,setItems]=useState([{description:'',account_id:'',quantity:1,unit_price:0,tax_rate:18}]);
  const [errors,setErrors]=useState<any>({});
  const addItem=()=>setItems([...items,{description:'',account_id:'',quantity:1,unit_price:0,tax_rate:18}]);
  const removeItem=(i:number)=>setItems(items.filter((_,idx)=>idx!==i));
  const updateItem=(i:number,k:string,v:any)=>setItems(items.map((it,idx)=>idx===i?{...it,[k]:v}:it));
  const total=items.reduce((s,it)=>s+(it.quantity*it.unit_price*(1+it.tax_rate/100)),0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    router.post(route('purchasing.invoices.store'),{supplier_id:supplierId,supplier_invoice_number:supplierInvNum,invoice_date:invoiceDate,due_date:dueDate||null,items:items.map(it=>({...it,account_id:it.account_id||null}))},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Facture fournisseur" onClose={onClose} size="lg">
    <form onSubmit={submit} className="space-y-3">
      <div className="grid grid-cols-2 gap-3">
        <Field label="Fournisseur *" error={errors.supplier_id}>
          <select value={supplierId} onChange={e=>setSupplierId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
            <option value="">—</option>{suppliers.map(s=><option key={s.id} value={s.id}>{s.code} - {s.name}</option>)}
          </select></Field>
        <Field label="N° facture fournisseur"><input value={supplierInvNum} onChange={e=>setSupplierInvNum(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" placeholder="N° sur la facture"/></Field>
      </div>
      <div className="grid grid-cols-2 gap-3">
        <Field label="Date facture *" error={errors.invoice_date}><input type="date" value={invoiceDate} onChange={e=>setInvoiceDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
        <Field label="Échéance"><input type="date" value={dueDate} onChange={e=>setDueDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
      </div>
      <div>
        <div className="flex justify-between items-center mb-2">
          <label className="block text-sm font-medium text-gray-700">Lignes *</label>
          <button type="button" onClick={addItem} className="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">+ Ligne</button>
        </div>
        <div className="space-y-2 max-h-64 overflow-y-auto">
          {items.map((it,i)=>(
            <div key={i} className="grid grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded">
              <input value={it.description} onChange={e=>updateItem(i,'description',e.target.value)} placeholder="Description" className="col-span-3 rounded-md border-gray-300 text-sm" required/>
              <select value={it.account_id} onChange={e=>updateItem(i,'account_id',e.target.value)} className="col-span-3 rounded-md border-gray-300 text-xs">
                <option value="">Compte par défaut</option>{expenseAccounts.map(a=><option key={a.id} value={a.id}>{a.number} - {a.name}</option>)}
              </select>
              <input type="number" step="0.01" min="0" value={it.quantity} onChange={e=>updateItem(i,'quantity',+e.target.value)} placeholder="Qté" className="col-span-1 rounded-md border-gray-300 text-sm" required/>
              <input type="number" step="0.01" min="0" value={it.unit_price} onChange={e=>updateItem(i,'unit_price',+e.target.value)} placeholder="PU" className="col-span-2 rounded-md border-gray-300 text-sm" required/>
              <select value={it.tax_rate} onChange={e=>updateItem(i,'tax_rate',+e.target.value)} className="col-span-2 rounded-md border-gray-300 text-sm">
                <option value="0">0%</option><option value="9">9%</option><option value="18">18%</option>
              </select>
              <button type="button" onClick={()=>removeItem(i)} className="col-span-1 text-red-600 hover:text-red-800">✕</button>
            </div>))}
        </div>
      </div>
      <div className="text-right font-bold text-lg border-t pt-2">Total TTC : {formatMoney(total)}</div>
      <ModalFooter onClose={onClose}/>
    </form></ModalShell>);
}

/* ===== ONGLET PAIEMENTS ===== */
function PaymentsTab({payments,suppliers,invoices}:{payments:Payment[];suppliers:Supplier[];invoices:Invoice[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Payment|null>(null);
  const unpaidInvoices=invoices.filter(i=>i.remaining>0);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Paiement</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Fournisseur</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Facture liée</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Mode</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Montant</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">État</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{payments.length===0?<tr><td colSpan={8} className="p-8 text-center text-gray-500">Aucun paiement.</td></tr>:payments.map(p=>(
        <tr key={p.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{p.reference}</td>
          <td className="p-2 font-medium">{p.supplier.name}</td>
          <td className="p-2 text-xs">{p.invoice_reference}</td>
          <td className="p-2 text-xs">{new Date(p.payment_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-xs">{p.payment_method==='bank'?'Virement':p.payment_method==='cash'?'Espèces':'Chèque'}</td>
          <td className="p-2 text-right font-mono font-semibold">{formatMoney(p.amount)}</td>
          <td className="p-2 text-center">{p.is_posted?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">📊 Comptabilisé</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}</td>
          <td className="p-2 text-right">
            {!p.is_posted&&<button onClick={()=>router.post(route('purchasing.payments.post',p.id))} className="text-purple-600 hover:underline text-xs mr-2" title="Comptabiliser">📊</button>}
            {!p.is_posted&&<button onClick={()=>setDel(p)} className="text-red-600 hover:underline text-xs">🗑</button>}
          </td>
        </tr>))}</tbody></table></div>
    {modal&&<PaymentModal suppliers={suppliers} unpaidInvoices={unpaidInvoices} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('purchasing.payments.destroy',del.id))}/>}
  </div>);
}

function PaymentModal({suppliers,unpaidInvoices,onClose}:{suppliers:Supplier[];unpaidInvoices:Invoice[];onClose:()=>void}){
  const [supplierId,setSupplierId]=useState('');
  const [invoiceId,setInvoiceId]=useState('');
  const [paymentDate,setPaymentDate]=useState(new Date().toISOString().slice(0,10));
  const [method,setMethod]=useState('bank');
  const [amount,setAmount]=useState(0);
  const [errors,setErrors]=useState<any>({});
  const selectedInvoice=unpaidInvoices.find(i=>i.id===+invoiceId);
  useEffect(()=>{if(selectedInvoice){setSupplierId(String(selectedInvoice.supplier.id));setAmount(selectedInvoice.remaining);}},[invoiceId]);
  const supplierInvoices=unpaidInvoices.filter(i=>!supplierId||i.supplier.id===+supplierId);
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    router.post(route('purchasing.payments.store'),{supplier_id:supplierId,purchase_invoice_id:invoiceId||null,payment_date:paymentDate,payment_method:method,amount},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Paiement fournisseur" onClose={onClose}>
    <form onSubmit={submit} className="space-y-3">
      <Field label="Facture à lettrer (optionnel)">
        <select value={invoiceId} onChange={e=>setInvoiceId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
          <option value="">— Paiement sans facture —</option>
          {supplierInvoices.map(i=><option key={i.id} value={i.id}>{i.reference} - {i.supplier.name} (reste {formatMoney(i.remaining)})</option>)}
        </select>
      </Field>
      {!invoiceId&&<Field label="Fournisseur *" error={errors.supplier_id}>
        <select value={supplierId} onChange={e=>setSupplierId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
          <option value="">—</option>{suppliers.map(s=><option key={s.id} value={s.id}>{s.code} - {s.name}</option>)}
        </select></Field>}
      <div className="grid grid-cols-2 gap-3">
        <Field label="Date *" error={errors.payment_date}><input type="date" value={paymentDate} onChange={e=>setPaymentDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
        <Field label="Mode">
          <select value={method} onChange={e=>setMethod(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
            <option value="bank">Virement bancaire</option><option value="cash">Espèces</option><option value="check">Chèque</option>
          </select></Field>
      </div>
      <Field label="Montant *" error={errors.amount}>
        <input type="number" step="0.01" min="0" value={amount} onChange={e=>setAmount(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm font-mono" required/>
        {selectedInvoice&&<p className="text-xs text-gray-500 mt-1">Reste à payer : {formatMoney(selectedInvoice.remaining)}</p>}
      </Field>
      <ModalFooter onClose={onClose}/>
    </form></ModalShell>);
}

/* ===== GÉNÉRIQUES ===== */
function ModalShell({title,children,onClose,size='md'}:{title:string;children:React.ReactNode;onClose:()=>void;size?:'md'|'lg'}){
  const maxW=size==='lg'?'max-w-4xl':'max-w-md';
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
    <div className={'bg-white rounded-lg shadow-2xl '+maxW+' w-full max-h-[90vh] overflow-y-auto'} onClick={e=>e.stopPropagation()}>
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
