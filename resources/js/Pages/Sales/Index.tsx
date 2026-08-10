import ErpLayout from '@/Layouts/ErpLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

interface Client { id:number; code:string; name:string; contact_name:string|null; email:string|null; phone:string|null; tax_number:string|null; account_number:string|null; is_active:boolean; }
interface Order { id:number; reference:string; client:{id:number;name:string}; order_date:string; validity_date:string|null; status:string; total_ttc:number; }
interface Invoice { id:number; reference:string; client:{id:number;name:string}; invoice_date:string; due_date:string|null; status:string; total_ttc:number; amount_paid:number; remaining:number; is_posted:boolean; }
interface Payment { id:number; reference:string; client:{id:number;name:string}; invoice_reference:string; payment_date:string; payment_method:string; amount:number; is_posted:boolean; }
interface Account { id:number; number:string; name:string; }
interface Props { clients:Client[]; orders:Order[]; invoices:Invoice[]; payments:Payment[]; revenueAccounts:Account[]; stats:any; initialTab:string; }

type TabKey='clients'|'orders'|'invoices'|'payments';
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';
const ORDER_STATUS: Record<string,string> = { draft:'Brouillon', sent:'Envoyé', accepted:'Accepté', refused:'Refusé', invoiced:'Facturé' };
const INVOICE_STATUS: Record<string,{label:string;color:string}> = { draft:{label:'Brouillon',color:'bg-gray-100 text-gray-800'}, validated:{label:'Validée',color:'bg-blue-100 text-blue-800'}, paid:{label:'Payée',color:'bg-green-100 text-green-800'}, cancelled:{label:'Annulée',color:'bg-red-100 text-red-800'} };

export default function Index(p:Props){
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'clients');
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  const flash=(usePage().props as any).flash;
  const tabs=[
    {key:'clients' as TabKey,label:'Clients',icon:'🤝'},
    {key:'orders' as TabKey,label:'Devis',icon:'📑'},
    {key:'invoices' as TabKey,label:'Factures',icon:'🧾'},
    {key:'payments' as TabKey,label:'Encaissements',icon:'💰'},
  ];
  return (
    <ErpLayout>
      <Head title="Ventes"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🛍️ Module Ventes</h1>
        <p className="text-sm text-gray-500 mt-1">Clients, Devis, Factures, Encaissements avec comptabilisation automatique</p></div>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-indigo-500"><p className="text-xs text-gray-500 uppercase">Clients</p><p className="text-xl font-bold">{p.stats.clients_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-blue-500"><p className="text-xs text-gray-500 uppercase">Devis</p><p className="text-xl font-bold">{p.stats.orders_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-purple-500"><p className="text-xs text-gray-500 uppercase">Factures</p><p className="text-xl font-bold">{p.stats.invoices_count}</p></div>
          <div className="p-3 rounded-lg bg-white shadow-sm border-l-4 border-orange-500"><p className="text-xs text-gray-500 uppercase">À encaisser</p><p className="text-lg font-bold text-orange-700">{formatMoney(p.stats.uncollected_total)}</p></div>
        </div>
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
          {tab==='clients'&&<ClientsTab clients={p.clients}/>}
          {tab==='orders'&&<OrdersTab orders={p.orders} clients={p.clients}/>}
          {tab==='invoices'&&<InvoicesTab invoices={p.invoices} clients={p.clients} revenueAccounts={p.revenueAccounts}/>}
          {tab==='payments'&&<PaymentsTab payments={p.payments} clients={p.clients} invoices={p.invoices}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

function ClientsTab({clients}:{clients:Client[]}){
  const [search,setSearch]=useState('');
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Client}>(null);
  const [del,setDel]=useState<Client|null>(null);
  const filtered=clients.filter(c=>!search||c.name.toLowerCase().includes(search.toLowerCase())||c.code.toLowerCase().includes(search.toLowerCase()));
  return (<div>
    <div className="flex gap-3 mb-4 items-center">
      <input type="text" placeholder="Rechercher..." value={search} onChange={e=>setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs"/>
      <button onClick={()=>setModal({mode:'create'})} className="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Client</button>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Nom</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Contact</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Email/Tel</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Compte</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{filtered.map(c=>(<tr key={c.id} className="hover:bg-gray-50">
        <td className="p-2 font-mono text-xs">{c.code}</td><td className="p-2 font-medium">{c.name}</td>
        <td className="p-2 text-xs">{c.contact_name||'—'}</td><td className="p-2 text-xs">{c.email||c.phone||'—'}</td>
        <td className="p-2 text-xs font-mono">{c.account_number||'—'}</td>
        <td className="p-2 text-right"><button onClick={()=>setModal({mode:'edit',item:c})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
        <button onClick={()=>setDel(c)} className="text-red-600 hover:underline text-xs">🗑</button></td>
      </tr>))}</tbody></table></div>
    {modal&&<ClientModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('sales.clients.destroy',del.id))}/>}
  </div>);
}
function ClientModal({mode,item,onClose}:{mode:'create'|'edit';item?:Client;onClose:()=>void}){
  const [form,setForm]=useState({code:item?.code||'',name:item?.name||'',contact_name:item?.contact_name||'',email:item?.email||'',phone:item?.phone||'',tax_number:item?.tax_number||'',account_number:item?.account_number||''});
  const [errors,setErrors]=useState<any>({});
  const submit=(e:React.FormEvent)=>{e.preventDefault();const o={onError:(er:any)=>setErrors(er),onSuccess:onClose};
    if(mode==='create')router.post(route('sales.clients.store'),form,o);else router.put(route('sales.clients.update',item!.id),form,o);};
  const set=(k:string,v:any)=>setForm(f=>({...f,[k]:v}));
  return (<ModalShell title={mode==='create'?'➕ Client':'✏️ Client'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div className="grid grid-cols-2 gap-3"><Field label="Code *" error={errors.code}><input value={form.code} onChange={e=>set('code',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
    <Field label="Nom *" error={errors.name}><input value={form.name} onChange={e=>set('name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field></div>
    <Field label="Contact"><input value={form.contact_name} onChange={e=>set('contact_name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <div className="grid grid-cols-2 gap-3"><Field label="Email"><input type="email" value={form.email} onChange={e=>set('email',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <Field label="Téléphone"><input value={form.phone} onChange={e=>set('phone',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <div className="grid grid-cols-2 gap-3"><Field label="N° fiscal"><input value={form.tax_number} onChange={e=>set('tax_number',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
    <Field label="Compte 411"><input value={form.account_number} onChange={e=>set('account_number',e.target.value)} placeholder="411XXXXX" className="w-full rounded-md border-gray-300 text-sm font-mono"/></Field></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function OrdersTab({orders,clients}:{orders:Order[];clients:Client[]}){
  const [modal,setModal]=useState(false);const [del,setDel]=useState<Order|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Devis</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Client</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Total TTC</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{orders.length===0?<tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucun devis.</td></tr>:orders.map(o=>(
        <tr key={o.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{o.reference}</td><td className="p-2 font-medium">{o.client.name}</td>
          <td className="p-2 text-xs">{new Date(o.order_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(o.total_ttc)}</td>
          <td className="p-2 text-center"><select value={o.status} onChange={e=>router.put(route('sales.orders.status',o.id),{status:e.target.value})} className="rounded-md border-gray-300 text-xs py-1">
            {Object.entries(ORDER_STATUS).map(([k,v])=><option key={k} value={k}>{v}</option>)}</select></td>
          <td className="p-2 text-right">{o.status==='draft'&&<button onClick={()=>setDel(o)} className="text-red-600 hover:underline text-xs">🗑</button>}</td>
        </tr>))}</tbody></table></div>
    {modal&&<OrderModal clients={clients} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('sales.orders.destroy',del.id))}/>}
  </div>);
}
function OrderModal({clients,onClose}:{clients:Client[];onClose:()=>void}){
  const [clientId,setClientId]=useState('');const [validity,setValidity]=useState('');
  const [items,setItems]=useState([{description:'',quantity:1,unit_price:0,tax_rate:18}]);
  const [errors,setErrors]=useState<any>({});
  const addItem=()=>setItems([...items,{description:'',quantity:1,unit_price:0,tax_rate:18}]);
  const removeItem=(i:number)=>setItems(items.filter((_,idx)=>idx!==i));
  const updateItem=(i:number,k:string,v:any)=>setItems(items.map((it,idx)=>idx===i?{...it,[k]:v}:it));
  const total=items.reduce((s,it)=>s+(it.quantity*it.unit_price*(1+it.tax_rate/100)),0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('sales.orders.store'),{client_id:clientId,validity_date:validity||null,items},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Devis" onClose={onClose} size="lg"><form onSubmit={submit} className="space-y-3">
    <div className="grid grid-cols-2 gap-3">
      <Field label="Client *" error={errors.client_id}><select value={clientId} onChange={e=>setClientId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required><option value="">—</option>{clients.map(c=><option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}</select></Field>
      <Field label="Validité"><input type="date" value={validity} onChange={e=>setValidity(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <ItemsEditor items={items} addItem={addItem} removeItem={removeItem} updateItem={updateItem}/>
    <div className="text-right font-bold text-lg border-t pt-2">Total TTC : {formatMoney(total)}</div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function InvoicesTab({invoices,clients,revenueAccounts}:{invoices:Invoice[];clients:Client[];revenueAccounts:Account[]}){
  const [modal,setModal]=useState(false);const [del,setDel]=useState<Invoice|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Facture</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Client</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Total TTC</th>
        <th className="p-2 text-right text-xs text-gray-600 uppercase">Payé</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Reste</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{invoices.length===0?<tr><td colSpan={8} className="p-8 text-center text-gray-500">Aucune facture.</td></tr>:invoices.map(i=>{const cfg=INVOICE_STATUS[i.status]||INVOICE_STATUS.draft;return(
        <tr key={i.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{i.reference}</td><td className="p-2 font-medium">{i.client.name}</td>
          <td className="p-2 text-xs">{new Date(i.invoice_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-right font-mono">{formatMoney(i.total_ttc)}</td>
          <td className="p-2 text-right font-mono text-green-700">{formatMoney(i.amount_paid)}</td>
          <td className="p-2 text-right font-mono text-orange-700 font-semibold">{formatMoney(i.remaining)}</td>
          <td className="p-2 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+cfg.color}>{cfg.label}</span>{i.is_posted&&<span className="ml-1 text-xs text-purple-700">📊</span>}</td>
          <td className="p-2 text-right">{!i.is_posted&&i.status==='draft'&&<><button onClick={()=>router.post(route('sales.invoices.post',i.id))} className="text-purple-600 hover:underline text-xs mr-2" title="Comptabiliser">📊</button>
          <button onClick={()=>setDel(i)} className="text-red-600 hover:underline text-xs">🗑</button></>}</td>
        </tr>);})}</tbody></table></div>
    {modal&&<InvoiceModal clients={clients} revenueAccounts={revenueAccounts} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('sales.invoices.destroy',del.id))}/>}
  </div>);
}
function InvoiceModal({clients,revenueAccounts,onClose}:{clients:Client[];revenueAccounts:Account[];onClose:()=>void}){
  const [clientId,setClientId]=useState('');const [invoiceDate,setInvoiceDate]=useState(new Date().toISOString().slice(0,10));const [dueDate,setDueDate]=useState('');
  const [items,setItems]=useState([{description:'',account_id:'',quantity:1,unit_price:0,tax_rate:18}]);
  const [errors,setErrors]=useState<any>({});
  const addItem=()=>setItems([...items,{description:'',account_id:'',quantity:1,unit_price:0,tax_rate:18}]);
  const removeItem=(i:number)=>setItems(items.filter((_,idx)=>idx!==i));
  const updateItem=(i:number,k:string,v:any)=>setItems(items.map((it,idx)=>idx===i?{...it,[k]:v}:it));
  const total=items.reduce((s,it)=>s+(it.quantity*it.unit_price*(1+it.tax_rate/100)),0);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('sales.invoices.store'),{client_id:clientId,invoice_date:invoiceDate,due_date:dueDate||null,items:items.map(it=>({...it,account_id:it.account_id||null}))},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Facture de vente" onClose={onClose} size="lg"><form onSubmit={submit} className="space-y-3">
    <div className="grid grid-cols-3 gap-3">
      <Field label="Client *" error={errors.client_id}><select value={clientId} onChange={e=>setClientId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required><option value="">—</option>{clients.map(c=><option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}</select></Field>
      <Field label="Date *" error={errors.invoice_date}><input type="date" value={invoiceDate} onChange={e=>setInvoiceDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
      <Field label="Échéance"><input type="date" value={dueDate} onChange={e=>setDueDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
    <ItemsEditor items={items} addItem={addItem} removeItem={removeItem} updateItem={updateItem} accounts={revenueAccounts} accountLabel="Compte produit"/>
    <div className="text-right font-bold text-lg border-t pt-2">Total TTC : {formatMoney(total)}</div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

function PaymentsTab({payments,clients,invoices}:{payments:Payment[];clients:Client[];invoices:Invoice[]}){
  const [modal,setModal]=useState(false);const [del,setDel]=useState<Payment|null>(null);
  const unpaid=invoices.filter(i=>i.remaining>0);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Encaissement</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Référence</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Client</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Facture</th><th className="p-2 text-left text-xs text-gray-600 uppercase">Date</th>
        <th className="p-2 text-left text-xs text-gray-600 uppercase">Mode</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Montant</th>
        <th className="p-2 text-center text-xs text-gray-600 uppercase">État</th><th className="p-2 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{payments.length===0?<tr><td colSpan={8} className="p-8 text-center text-gray-500">Aucun encaissement.</td></tr>:payments.map(p=>(
        <tr key={p.id} className="hover:bg-gray-50">
          <td className="p-2 font-mono text-xs font-semibold">{p.reference}</td><td className="p-2 font-medium">{p.client.name}</td>
          <td className="p-2 text-xs">{p.invoice_reference}</td><td className="p-2 text-xs">{new Date(p.payment_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-2 text-xs">{p.payment_method}</td><td className="p-2 text-right font-mono font-semibold">{formatMoney(p.amount)}</td>
          <td className="p-2 text-center">{p.is_posted?<span className="px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">📊</span>:<span className="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Brouillon</span>}</td>
          <td className="p-2 text-right">{!p.is_posted&&<><button onClick={()=>router.post(route('sales.payments.post',p.id))} className="text-purple-600 hover:underline text-xs mr-2">📊</button>
          <button onClick={()=>setDel(p)} className="text-red-600 hover:underline text-xs">🗑</button></>}</td>
        </tr>))}</tbody></table></div>
    {modal&&<PaymentModal clients={clients} unpaid={unpaid} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.reference} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('sales.payments.destroy',del.id))}/>}
  </div>);
}
function PaymentModal({clients,unpaid,onClose}:{clients:Client[];unpaid:Invoice[];onClose:()=>void}){
  const [clientId,setClientId]=useState('');const [invoiceId,setInvoiceId]=useState('');
  const [paymentDate,setPaymentDate]=useState(new Date().toISOString().slice(0,10));const [method,setMethod]=useState('bank');const [amount,setAmount]=useState(0);
  const [errors,setErrors]=useState<any>({});
  const sel=unpaid.find(i=>i.id===+invoiceId);
  useEffect(()=>{if(sel){setClientId(String(sel.client.id));setAmount(sel.remaining);}},[invoiceId]);
  const clientInvoices=unpaid.filter(i=>!clientId||i.client.id===+clientId);
  const submit=(e:React.FormEvent)=>{e.preventDefault();router.post(route('sales.payments.store'),{client_id:clientId,sales_invoice_id:invoiceId||null,payment_date:paymentDate,payment_method:method,amount},{onError:(er:any)=>setErrors(er),onSuccess:onClose});};
  return (<ModalShell title="➕ Encaissement" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Field label="Facture à lettrer"><select value={invoiceId} onChange={e=>setInvoiceId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">— Sans facture —</option>{clientInvoices.map(i=><option key={i.id} value={i.id}>{i.reference} - {i.client.name} (reste {formatMoney(i.remaining)})</option>)}</select></Field>
    {!invoiceId&&<Field label="Client *" error={errors.client_id}><select value={clientId} onChange={e=>setClientId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required><option value="">—</option>{clients.map(c=><option key={c.id} value={c.id}>{c.code} - {c.name}</option>)}</select></Field>}
    <div className="grid grid-cols-2 gap-3">
      <Field label="Date *" error={errors.payment_date}><input type="date" value={paymentDate} onChange={e=>setPaymentDate(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
      <Field label="Mode"><select value={method} onChange={e=>setMethod(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="bank">Virement</option><option value="cash">Espèces</option><option value="check">Chèque</option><option value="mobile">Mobile Money</option></select></Field></div>
    <Field label="Montant *" error={errors.amount}><input type="number" step="0.01" min="0" value={amount} onChange={e=>setAmount(+e.target.value)} className="w-full rounded-md border-gray-300 text-sm font-mono" required/>{sel&&<p className="text-xs text-gray-500 mt-1">Reste à payer : {formatMoney(sel.remaining)}</p>}</Field>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

/* Éditeur de lignes partagé (devis + factures) */
function ItemsEditor({items,addItem,removeItem,updateItem,accounts,accountLabel}:{items:any[];addItem:()=>void;removeItem:(i:number)=>void;updateItem:(i:number,k:string,v:any)=>void;accounts?:Account[];accountLabel?:string}){
  return (<div>
    <div className="flex justify-between items-center mb-2">
      <label className="block text-sm font-medium text-gray-700">Lignes *</label>
      <button type="button" onClick={addItem} className="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">+ Ligne</button></div>
    <div className="space-y-2 max-h-64 overflow-y-auto">
      {items.map((it,i)=>(
        <div key={i} className="grid grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded">
          <input value={it.description} onChange={e=>updateItem(i,'description',e.target.value)} placeholder="Description" className={(accounts?'col-span-3':'col-span-5')+' rounded-md border-gray-300 text-sm'} required/>
          {accounts&&<select value={it.account_id} onChange={e=>updateItem(i,'account_id',e.target.value)} className="col-span-3 rounded-md border-gray-300 text-xs"><option value="">{accountLabel||'Compte'}</option>{accounts.map(a=><option key={a.id} value={a.id}>{a.number} - {a.name}</option>)}</select>}
          <input type="number" step="0.01" min="0" value={it.quantity} onChange={e=>updateItem(i,'quantity',+e.target.value)} placeholder="Qté" className={(accounts?'col-span-1':'col-span-2')+' rounded-md border-gray-300 text-sm'} required/>
          <input type="number" step="0.01" min="0" value={it.unit_price} onChange={e=>updateItem(i,'unit_price',+e.target.value)} placeholder="PU" className="col-span-2 rounded-md border-gray-300 text-sm" required/>
          <select value={it.tax_rate} onChange={e=>updateItem(i,'tax_rate',+e.target.value)} className="col-span-2 rounded-md border-gray-300 text-sm"><option value="0">0%</option><option value="9">9%</option><option value="18">18%</option></select>
          <button type="button" onClick={()=>removeItem(i)} className="col-span-1 text-red-600 hover:text-red-800">✕</button>
        </div>))}
    </div>
  </div>);
}

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
