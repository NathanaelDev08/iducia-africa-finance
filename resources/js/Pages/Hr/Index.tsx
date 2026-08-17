import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, Fragment } from 'react';

interface Employee { id:number; matricule:string; full_name:string; email:string|null; phone:string|null; sex:string|null; hire_date:string; status:string; department:{id:number;name:string}|null; position:{id:number;name:string}|null; }
interface Department { id:number; code:string; name:string; is_active:boolean; positions_count:number; employees_count:number; }
interface Position { id:number; code:string; name:string; is_active:boolean; department:{id:number;name:string}|null; employees_count:number; employees:{id:number;matricule:string;full_name:string}[]; }
interface ContractType { id:number; code:string; name:string; is_active:boolean; }
interface Contract { id:number; contract_number:string|null; employee:{id:number;full_name:string;matricule:string;dependents_count:number;children_count:number}; contract_type:string; contract_type_id:number|null; start_date:string; end_date:string|null; base_salary:number; salaire_categoriel:number|null; sursalaire:number|null; has_cmu:boolean; has_cnps:boolean; status:string; }
interface Leave { id:number; employee:{id:number;full_name:string;matricule:string}; leave_type:string; start_date:string; end_date:string; days_count:number; status:string; }
interface Doc { id:number; employee:{id:number;full_name:string;matricule:string}; document_type:string; name:string; file_path:string|null; expires_at:string|null; status:string; }
interface AllEmployee { id:number; full_name:string; matricule:string; dependents_count:number; children_count:number; }
interface LeaveBalance { id:number; matricule:string; full_name:string; balance:number; threshold_reached:boolean; }
interface Props { employees:Employee[]; departments:Department[]; positions:Position[]; contractTypes:ContractType[]; contracts?:Contract[]; leaves?:Leave[]; documents?:Doc[]; allEmployees:AllEmployee[]; allDepartments:{id:number;name:string}[]; stats:any; initialTab:string; cnpsEmployeeRate:number; leaveBalances?:LeaveBalance[]; }

type TabKey='employes'|'contrats'|'conges'|'documents'|'departements'|'postes'|'types';

const EMP_STATUS: Record<string,{label:string;color:string}> = { active:{label:'Actif',color:'bg-green-100 text-green-800'}, inactive:{label:'Inactif',color:'bg-gray-100 text-gray-800'}, suspended:{label:'Suspendu',color:'bg-yellow-100 text-yellow-800'}, terminated:{label:'Terminé',color:'bg-red-100 text-red-800'} };
const LEAVE_TYPE: Record<string,string> = { annual:'Congé annuel', sick:'Maladie', maternity:'Maternité', unpaid:'Sans solde' };
const formatMoney=(v:number)=>(v||0).toLocaleString('fr-FR')+' FCFA';

export default function Index(p:Props){
  const contracts = p.contracts || [];
  const leaves = p.leaves || [];
  const documents = p.documents || [];
  const [tab,setTab]=useState<TabKey>((p.initialTab as TabKey)||'employes');
  useEffect(()=>{const u=new URL(window.location.href);u.searchParams.set('tab',tab);window.history.replaceState({},'',u.toString());},[tab]);
  const tabs=[
    {key:'employes' as TabKey,label:'Employés',icon:'👥'},
    {key:'contrats' as TabKey,label:'Contrats',icon:'📝'},
    {key:'conges' as TabKey,label:'Congés',icon:'🏖️'},
    {key:'documents' as TabKey,label:'Documents',icon:'📎'},
    {key:'departements' as TabKey,label:'Départements',icon:'🏢'},
    {key:'postes' as TabKey,label:'Postes',icon:'💼'},
    {key:'types' as TabKey,label:'Types',icon:'📋'},
  ];
  return (
    <ErpLayout>
      <Head title="Ressources Humaines"/>
      <div className="py-6"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">👥 Ressources Humaines</h1>
        <p className="text-sm text-gray-500 mt-1">CRUD complet : Employés, Contrats, Congés, Documents, Référentiels</p></div>
        <div className="bg-white rounded-t-lg shadow-sm border-b"><nav className="flex overflow-x-auto">
          {tabs.map(t=>{const a=tab===t.key;return(
            <button key={t.key} onClick={()=>setTab(t.key)} className={'relative whitespace-nowrap flex-1 py-4 px-3 text-center text-sm font-medium hover:bg-gray-50 '+(a?'text-gray-900 font-semibold':'text-gray-500')}>
              <span className="mr-1">{t.icon}</span>{t.label}
              <span className={'absolute inset-x-0 bottom-0 h-0.5 '+(a?'bg-indigo-600':'bg-transparent')}/>
            </button>);})}
        </nav></div>
        <div className="bg-white rounded-b-lg shadow-sm p-6">
          {tab==='employes'&&<EmployesTab {...p}/>}
          {tab==='contrats'&&<ContratsTab contracts={contracts} allEmployees={p.allEmployees} contractTypes={p.contractTypes} cnpsEmployeeRate={p.cnpsEmployeeRate}/>}
          {tab==='conges'&&<CongesTab leaves={leaves} allEmployees={p.allEmployees} leaveBalances={p.leaveBalances}/>}
          {tab==='documents'&&<DocumentsTab documents={documents} allEmployees={p.allEmployees}/>}
          {tab==='departements'&&<DepartementsTab data={p.departments}/>}
          {tab==='postes'&&<PostesTab data={p.positions} allDepartments={p.allDepartments}/>}
          {tab==='types'&&<TypesTab data={p.contractTypes}/>}
        </div>
      </div></div>
    </ErpLayout>
  );
}

/* ===== EMPLOYÉS ===== */
function EmployesTab({employees,stats,allDepartments}:any){
  const [search,setSearch]=useState('');const [status,setStatus]=useState('');
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Employee}>(null);
  const [del,setDel]=useState<Employee|null>(null);
  const filtered=employees.filter((e:Employee)=>(!search||e.full_name.toLowerCase().includes(search.toLowerCase())||e.matricule.toLowerCase().includes(search.toLowerCase()))&&(!status||e.status===status));
  return (<div>
    <div className="grid grid-cols-3 gap-4 mb-4">
      <div className="p-3 rounded-lg bg-indigo-50 border-l-4 border-indigo-500"><p className="text-xs text-gray-500 uppercase">Total</p><p className="text-xl font-bold">{stats.total}</p></div>
      <div className="p-3 rounded-lg bg-green-50 border-l-4 border-green-500"><p className="text-xs text-gray-500 uppercase">Actifs</p><p className="text-xl font-bold text-green-700">{stats.active}</p></div>
      <div className="p-3 rounded-lg bg-gray-50 border-l-4 border-gray-400"><p className="text-xs text-gray-500 uppercase">Inactifs</p><p className="text-xl font-bold text-gray-600">{stats.inactive}</p></div>
    </div>
    <div className="flex flex-wrap gap-3 mb-4 items-center">
      <input type="text" placeholder="Rechercher..." value={search} onChange={e=>setSearch(e.target.value)} className="rounded-md border-gray-300 text-sm max-w-xs"/>
      <select value={status} onChange={e=>setStatus(e.target.value)} className="rounded-md border-gray-300 text-sm"><option value="">Tous</option>{Object.entries(EMP_STATUS).map(([k,v])=><option key={k} value={k}>{v.label}</option>)}</select>
      <button onClick={()=>setModal({mode:'create'})} className="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Nouvel employé</button>
    </div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Matricule</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Employé</th>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Département</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Poste</th>
        <th className="p-3 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{filtered.length===0?<tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucun employé.</td></tr>:filtered.map((e:Employee)=>{const c=EMP_STATUS[e.status]||EMP_STATUS.inactive;return(
        <tr key={e.id} className="hover:bg-gray-50">
          <td className="p-3 font-mono text-xs">{e.matricule}</td><td className="p-3 font-medium">{e.full_name}</td>
          <td className="p-3 text-xs">{e.department?.name||'—'}</td><td className="p-3 text-xs">{e.position?.name||'—'}</td>
          <td className="p-3 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+c.color}>{c.label}</span></td>
          <td className="p-3 text-right">
            <Link href={route('hr.employees.show',e.id)} className="text-indigo-600 hover:underline text-xs mr-2">Voir</Link>
            <button onClick={()=>setModal({mode:'edit',item:e})} className="text-blue-600 hover:underline text-xs mr-2">✏️</button>
            <button onClick={()=>setDel(e)} className="text-red-600 hover:underline text-xs">🗑</button>
          </td></tr>);})}
      </tbody></table></div>
    {modal&&<EmployeeModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} departments={allDepartments} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.full_name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.employees.destroy',del.id))}/>}
  </div>);
}

/* ===== CONTRATS (CRUD) ===== */
function ContratsTab({contracts = [],allEmployees,contractTypes,cnpsEmployeeRate}:{contracts?:Contract[];allEmployees:AllEmployee[];contractTypes:ContractType[];cnpsEmployeeRate:number}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Contract}>(null);
  const [del,setDel]=useState<Contract|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Contrat</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">N°</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Employé</th>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Type</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Début</th>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Fin</th>
        <th className="p-3 text-right text-xs text-gray-600 uppercase">Catégoriel</th>
        <th className="p-3 text-right text-xs text-gray-600 uppercase">Sursalaire</th>
        <th className="p-3 text-right text-xs text-gray-600 uppercase">Salaire base</th>
        <th className="p-3 text-center text-xs text-gray-600 uppercase">Cotisations</th>
        <th className="p-3 text-center text-xs text-gray-600 uppercase">Statut</th><th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{contracts.length===0?<tr><td colSpan={11} className="p-8 text-center text-gray-500">Aucun contrat.</td></tr>:contracts.map(c=>(
        <tr key={c.id} className="hover:bg-gray-50">
          <td className="p-3 font-mono text-xs">{c.contract_number||'—'}</td><td className="p-3 font-medium">{c.employee.full_name}</td>
          <td className="p-3 text-xs">{c.contract_type}</td><td className="p-3 text-xs">{new Date(c.start_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-3 text-xs">{c.end_date?new Date(c.end_date).toLocaleDateString('fr-FR'):'CDI'}</td>
          <td className="p-3 text-right font-mono text-xs">{c.salaire_categoriel!=null?formatMoney(c.salaire_categoriel):'—'}</td>
          <td className="p-3 text-right font-mono text-xs">{c.sursalaire!=null?formatMoney(c.sursalaire):'—'}</td>
          <td className="p-3 text-right font-mono">{formatMoney(c.base_salary)}</td>
          <td className="p-3 text-center">
            <div className="flex gap-1 justify-center">
              {c.has_cnps&&<span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">CNPS</span>}
              {c.has_cmu&&<span className="px-1.5 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">CMU</span>}
              {!c.has_cnps&&!c.has_cmu&&<span className="text-xs text-gray-400">—</span>}
            </div>
          </td>
          <td className="p-3 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+(c.status==='active'?'bg-green-100 text-green-800':'bg-gray-100 text-gray-700')}>{c.status}</span></td>
          <td className="p-3 text-right"><button onClick={()=>setModal({mode:'edit',item:c})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
          <button onClick={()=>setDel(c)} className="text-red-600 hover:underline text-xs">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal&&<ContractModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} allEmployees={allEmployees} contractTypes={contractTypes} cnpsEmployeeRate={cnpsEmployeeRate} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={'contrat '+del.employee.full_name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.contracts.destroy',del.id))}/>}
  </div>);
}

function ContractModal({mode,item,allEmployees,contractTypes,cnpsEmployeeRate,onClose}:{mode:'create'|'edit';item?:Contract;allEmployees:AllEmployee[];contractTypes:ContractType[];cnpsEmployeeRate:number;onClose:()=>void}){
  const [employeeId,setEmployeeId]=useState('');
  const [typeId,setTypeId]=useState(item?.contract_type_id!=null?String(item.contract_type_id):'');
  const [number,setNumber]=useState(item?.contract_number||'');
  const [start,setStart]=useState(item?.start_date||new Date().toISOString().slice(0,10));
  const [end,setEnd]=useState(item?.end_date||'');
  const [salary,setSalary]=useState(item?item.base_salary!=null?String(item.base_salary):'0':'0');
  const [categoriel,setCategoriel]=useState(item?.salaire_categoriel!=null?String(item.salaire_categoriel):'');
  const [hasCmu,setHasCmu]=useState(item?.has_cmu??false);
  const [hasCnps,setHasCnps]=useState(item?.has_cnps??true);

  // Sursalaire = complément versé au-delà du salaire catégoriel (grille conventionnelle).
  // Affiché en lecture seule et recalculé en direct ; jamais négatif.
  const baseSalaryNum = parseFloat(salary)||0;
  const categorielNum = parseFloat(categoriel)||0;
  const sursalaireIsNegative = categorielNum > baseSalaryNum && categoriel !== '';
  const sursalaire = Math.max(0, baseSalaryNum - categorielNum);

  // Prévisualisation CMU : 500 FCFA x (1 salarié + nombre de personnes à charge).
  // On compte de préférence les enfants réellement enregistrés (table employee_children) ;
  // à défaut, on retombe sur le compteur "dependents_count" de l'employé.
  const selectedEmployee = allEmployees.find(e=>String(e.id)===String(employeeId)) || item?.employee;
  const dependentsForCmu = selectedEmployee
    ? (selectedEmployee.children_count>0 ? selectedEmployee.children_count : selectedEmployee.dependents_count)
    : 0;
  const cmuEstimate = 500 * (1 + dependentsForCmu);

  const submit=(e:React.FormEvent)=>{e.preventDefault();
    const payload={employee_id:employeeId,contract_type_id:typeId||null,contract_number:number,start_date:start,end_date:end||null,
      base_salary:salary,salaire_categoriel:categoriel||null,sursalaire:String(sursalaire),has_cmu:hasCmu,has_cnps:hasCnps};
    if(mode==='create')router.post(route('hr.contracts.store'),payload,{onSuccess:onClose});
    else router.put(route('hr.contracts.update',item!.id),{...payload,employee_id:undefined},{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Contrat':'✏️ Contrat'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    {mode==='create'&&<div><label className="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
      <select value={employeeId} onChange={e=>setEmployeeId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
        <option value="">—</option>{allEmployees.map((e)=><option key={e.id} value={e.id}>{e.matricule} - {e.full_name}</option>)}</select></div>}
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
      <select value={typeId} onChange={e=>setTypeId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
        <option value="">—</option>{contractTypes.map((t)=><option key={t.id} value={t.id}>{t.name}</option>)}</select></div>
    <Input label="N° contrat" value={number} onChange={setNumber}/>
    <div className="grid grid-cols-2 gap-3">
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Début *</label><input type="date" value={start} onChange={e=>setStart(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Fin</label><input type="date" value={end} onChange={e=>setEnd(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></div>
    </div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Salaire de base (total) *</label><input type="number" min="0" value={salary} onChange={e=>setSalary(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Salaire catégoriel</label><input type="number" min="0" value={categoriel} onChange={e=>setCategoriel(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" placeholder="Selon grille conventionnelle"/></div>
    <div>
      <label className="block text-sm font-medium text-gray-700 mb-1">Sursalaire (calculé)</label>
      <input type="text" readOnly value={formatMoney(sursalaire)} className="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600"/>
      {sursalaireIsNegative&&<p className="text-xs text-yellow-600 mt-1">⚠️ Le catégoriel dépasse le salaire de base : sursalaire ramené à 0.</p>}
    </div>
    <div className="border-t pt-3 space-y-2">
      <label className="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" checked={hasCnps} onChange={e=>setHasCnps(e.target.checked)} className="rounded border-gray-300"/>
        Cotisation CNPS
        <span className="text-xs text-gray-500 ml-1">(Taux salarié actuel : {cnpsEmployeeRate}%)</span>
      </label>
      <label className="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" checked={hasCmu} onChange={e=>setHasCmu(e.target.checked)} className="rounded border-gray-300"/>
        Couverture Maladie Universelle (CMU)
      </label>
      {hasCmu&&(
        <p className="text-xs text-teal-700 bg-teal-50 border border-teal-200 rounded-md px-2 py-1.5">
          Estimation CMU : 500 FCFA × (1 + {dependentsForCmu} personne(s) à charge) = <strong>{formatMoney(cmuEstimate)}</strong> / mois
        </p>
      )}
    </div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

/* ===== CONGÉS (workflow) ===== */
function CongesTab({leaves = [],allEmployees,leaveBalances = []}:{leaves?:Leave[];allEmployees:any[];leaveBalances?:LeaveBalance[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Leave|null>(null);
  return (<div>
    {leaveBalances.length>0&&<div className="mb-6">
      <h3 className="text-sm font-semibold text-gray-700 mb-2">🗓️ Solde de congés acquis (cumul par employé)</h3>
      <div className="overflow-x-auto border rounded-md"><table className="w-full text-sm">
        <thead className="bg-gray-50 border-b"><tr>
          <th className="p-3 text-left text-xs text-gray-600 uppercase">Employé</th>
          <th className="p-3 text-center text-xs text-gray-600 uppercase">Solde acquis</th>
          <th className="p-3 text-center text-xs text-gray-600 uppercase">Statut</th></tr></thead>
        <tbody className="divide-y">{leaveBalances.map(b=>(
          <tr key={b.id} className="hover:bg-gray-50">
            <td className="p-3 font-medium">{b.full_name}<span className="text-gray-400 ml-1 font-mono text-xs">({b.matricule})</span></td>
            <td className="p-3 text-center font-mono">{b.balance.toLocaleString('fr-FR',{maximumFractionDigits:1})} j</td>
            <td className="p-3 text-center">
              {b.threshold_reached
                ? <span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">✅ ≥ 30 j — à planifier</span>
                : <span className="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Normal</span>}
            </td>
          </tr>))}</tbody></table></div>
    </div>}
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Demande de congé</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Employé</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Type</th>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Du</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Au</th>
        <th className="p-3 text-center text-xs text-gray-600 uppercase">Jours</th><th className="p-3 text-center text-xs text-gray-600 uppercase">Statut</th>
        <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{leaves.length===0?<tr><td colSpan={7} className="p-8 text-center text-gray-500">Aucune demande.</td></tr>:leaves.map(l=>(
        <tr key={l.id} className="hover:bg-gray-50">
          <td className="p-3 font-medium">{l.employee.full_name}</td><td className="p-3 text-xs">{LEAVE_TYPE[l.leave_type]||l.leave_type}</td>
          <td className="p-3 text-xs">{new Date(l.start_date).toLocaleDateString('fr-FR')}</td><td className="p-3 text-xs">{new Date(l.end_date).toLocaleDateString('fr-FR')}</td>
          <td className="p-3 text-center">{l.days_count}</td>
          <td className="p-3 text-center"><span className={'px-2 py-1 rounded-full text-xs font-semibold '+(l.status==='approved'?'bg-green-100 text-green-800':l.status==='rejected'?'bg-red-100 text-red-800':'bg-yellow-100 text-yellow-800')}>{l.status==='approved'?'Approuvé':l.status==='rejected'?'Rejeté':'En attente'}</span></td>
          <td className="p-3 text-right">
            {l.status==='pending'&&<>
              <button onClick={()=>router.post(route('hr.leaves.approve',l.id))} className="text-green-600 hover:underline text-xs mr-2">✅</button>
              <button onClick={()=>router.post(route('hr.leaves.reject',l.id))} className="text-red-600 hover:underline text-xs mr-2">❌</button>
            </>}
            <button onClick={()=>setDel(l)} className="text-red-600 hover:underline text-xs">🗑</button>
          </td></tr>))}</tbody></table></div>
    {modal&&<LeaveModal allEmployees={allEmployees} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={'congé '+del.employee.full_name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.leaves.destroy',del.id))}/>}
  </div>);
}

function LeaveModal({allEmployees,onClose}:{allEmployees:any[];onClose:()=>void}){
  const [employeeId,setEmployeeId]=useState('');
  const [type,setType]=useState('annual');
  const [start,setStart]=useState(new Date().toISOString().slice(0,10));
  const [end,setEnd]=useState(new Date().toISOString().slice(0,10));
  const [reason,setReason]=useState('');
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    router.post(route('hr.leaves.store'),{employee_id:employeeId,leave_type:type,start_date:start,end_date:end,reason},{onSuccess:onClose});};
  return (<ModalShell title="➕ Demande de congé" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
      <select value={employeeId} onChange={e=>setEmployeeId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
        <option value="">—</option>{allEmployees.map((e:any)=><option key={e.id} value={e.id}>{e.matricule} - {e.full_name}</option>)}</select></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
      <select value={type} onChange={e=>setType(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
        {Object.entries(LEAVE_TYPE).map(([k,v])=><option key={k} value={k}>{v}</option>)}</select></div>
    <div className="grid grid-cols-2 gap-3">
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Début *</label><input type="date" value={start} onChange={e=>setStart(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
      <div><label className="block text-sm font-medium text-gray-700 mb-1">Fin *</label><input type="date" value={end} onChange={e=>setEnd(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    </div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Motif</label><textarea value={reason} onChange={e=>setReason(e.target.value)} rows={2} className="w-full rounded-md border-gray-300 text-sm"/></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

/* ===== DOCUMENTS (upload) ===== */
function DocumentsTab({documents = [],allEmployees}:{documents?:Doc[];allEmployees:any[]}){
  const [modal,setModal]=useState(false);
  const [del,setDel]=useState<Doc|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">📤 Ajouter document</button></div>
    <div className="overflow-x-auto"><table className="w-full text-sm">
      <thead className="bg-gray-50 border-b"><tr>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Employé</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Type</th>
        <th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Expiration</th>
        <th className="p-3 text-center text-xs text-gray-600 uppercase">Fichier</th><th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{documents.length===0?<tr><td colSpan={6} className="p-8 text-center text-gray-500">Aucun document.</td></tr>:documents.map(d=>(
        <tr key={d.id} className="hover:bg-gray-50">
          <td className="p-3 font-medium">{d.employee.full_name}</td><td className="p-3 text-xs">{d.document_type}</td>
          <td className="p-3">{d.name}</td><td className="p-3 text-xs">{d.expires_at?new Date(d.expires_at).toLocaleDateString('fr-FR'):'—'}</td>
          <td className="p-3 text-center">{d.file_path?<a href={'/storage/'+d.file_path} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:underline text-xs">⬇ Télécharger</a>:<span className="text-xs text-gray-400">—</span>}</td>
          <td className="p-3 text-right"><button onClick={()=>setDel(d)} className="text-red-600 hover:underline text-xs">🗑</button></td>
        </tr>))}</tbody></table></div>
    {modal&&<DocumentModal allEmployees={allEmployees} onClose={()=>setModal(false)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.documents.destroy',del.id))}/>}
  </div>);
}

function DocumentModal({allEmployees,onClose}:{allEmployees:any[];onClose:()=>void}){
  const [employeeId,setEmployeeId]=useState('');
  const [type,setType]=useState('CNI');
  const [name,setName]=useState('');
  const [expires,setExpires]=useState('');
  const [file,setFile]=useState<File|null>(null);
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    const fd=new FormData();
    fd.append('employee_id',employeeId);fd.append('document_type',type);fd.append('name',name||type);
    if(expires)fd.append('expires_at',expires);
    if(file)fd.append('file',file);
    router.post(route('hr.documents.store'),fd,{onSuccess:onClose});};
  return (<ModalShell title="📤 Ajouter un document" onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Employé *</label>
      <select value={employeeId} onChange={e=>setEmployeeId(e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required>
        <option value="">—</option>{allEmployees.map((e:any)=><option key={e.id} value={e.id}>{e.matricule} - {e.full_name}</option>)}</select></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
      <select value={type} onChange={e=>setType(e.target.value)} className="w-full rounded-md border-gray-300 text-sm">
        <option>CNI</option><option>CV</option><option>Diplôme</option><option>Contrat</option><option>Certificat médical</option><option>Autre</option></select></div>
    <Input label="Nom du document" value={name} onChange={setName}/>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Date d'expiration</label><input type="date" value={expires} onChange={e=>setExpires(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Fichier (PDF, image)</label><input type="file" onChange={e=>setFile(e.target.files?.[0]||null)} className="w-full text-sm"/></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

/* ===== RÉFÉRENTIELS (identiques à avant) ===== */
function DepartementsTab({data}:{data:Department[]}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Department}>(null);
  const [del,setDel]=useState<Department|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button></div>
    <table className="w-full text-sm"><thead className="bg-gray-50 border-b"><tr>
      <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
      <th className="p-3 text-center text-xs text-gray-600 uppercase">Postes</th><th className="p-3 text-center text-xs text-gray-600 uppercase">Employés</th>
      <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.map(d=>(<tr key={d.id} className="hover:bg-gray-50">
        <td className="p-3 font-mono text-xs">{d.code}</td><td className="p-3 font-medium">{d.name}</td>
        <td className="p-3 text-center">{d.positions_count}</td><td className="p-3 text-center">{d.employees_count}</td>
        <td className="p-3 text-right"><button onClick={()=>setModal({mode:'edit',item:d})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
        <button onClick={()=>setDel(d)} className="text-red-600 hover:underline text-xs">🗑</button></td></tr>))}</tbody></table>
    {modal&&<DeptModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.referentials.departments.destroy',del.id))}/>}
  </div>);
}
function PostesTab({data,allDepartments}:{data:Position[];allDepartments:any[]}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:Position}>(null);
  const [del,setDel]=useState<Position|null>(null);
  const [expanded,setExpanded]=useState<number|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button></div>
    <table className="w-full text-sm"><thead className="bg-gray-50 border-b"><tr>
      <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
      <th className="p-3 text-left text-xs text-gray-600 uppercase">Département</th><th className="p-3 text-center text-xs text-gray-600 uppercase">Employés</th>
      <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.map(p=>(<Fragment key={p.id}>
        <tr className="hover:bg-gray-50">
          <td className="p-3 font-mono text-xs">{p.code}</td><td className="p-3 font-medium">{p.name}</td>
          <td className="p-3 text-xs">{p.department?.name||'—'}</td>
          <td className="p-3 text-center">
            <button onClick={()=>setExpanded(expanded===p.id?null:p.id)} className="text-indigo-600 hover:underline text-xs" disabled={p.employees_count===0}>
              {p.employees_count} {p.employees_count>0&&(expanded===p.id?'▲':'▼')}
            </button>
          </td>
          <td className="p-3 text-right"><button onClick={()=>setModal({mode:'edit',item:p})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
          <button onClick={()=>setDel(p)} className="text-red-600 hover:underline text-xs">🗑</button></td></tr>
        {expanded===p.id&&p.employees_count>0&&(<tr key={p.id+'-detail'} className="bg-gray-50">
          <td colSpan={5} className="p-3">
            <ul className="flex flex-wrap gap-x-6 gap-y-1">
              {p.employees.map(e=>(<li key={e.id} className="text-xs">
                <Link href={route('hr.employees.show',e.id)} className="text-indigo-600 hover:underline">{e.full_name}</Link>
                <span className="text-gray-400 ml-1 font-mono">({e.matricule})</span>
              </li>))}
            </ul>
          </td></tr>)}
      </Fragment>))}</tbody></table>
    {modal&&<PosteModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} allDepartments={allDepartments} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.referentials.positions.destroy',del.id))}/>}
  </div>);
}
function TypesTab({data}:{data:ContractType[]}){
  const [modal,setModal]=useState<null|{mode:'create'}|{mode:'edit';item:ContractType}>(null);
  const [del,setDel]=useState<ContractType|null>(null);
  return (<div>
    <div className="flex justify-end mb-4"><button onClick={()=>setModal({mode:'create'})} className="bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-md">+ Ajouter</button></div>
    <table className="w-full text-sm"><thead className="bg-gray-50 border-b"><tr>
      <th className="p-3 text-left text-xs text-gray-600 uppercase">Code</th><th className="p-3 text-left text-xs text-gray-600 uppercase">Nom</th>
      <th className="p-3 text-right text-xs text-gray-600 uppercase">Actions</th></tr></thead>
      <tbody className="divide-y">{data.map(c=>(<tr key={c.id} className="hover:bg-gray-50">
        <td className="p-3 font-mono text-xs">{c.code}</td><td className="p-3 font-medium">{c.name}</td>
        <td className="p-3 text-right"><button onClick={()=>setModal({mode:'edit',item:c})} className="text-blue-600 hover:underline text-xs mr-3">✏️</button>
        <button onClick={()=>setDel(c)} className="text-red-600 hover:underline text-xs">🗑</button></td></tr>))}</tbody></table>
    {modal&&<TypeModal mode={modal.mode} item={modal.mode==='edit'?modal.item:undefined} onClose={()=>setModal(null)}/>}
    {del&&<ConfirmDelete label={del.name} onClose={()=>setDel(null)} onConfirm={()=>router.delete(route('hr.referentials.contract-types.destroy',del.id))}/>}
  </div>);
}

/* ===== MODALS référentiels ===== */
function DeptModal({mode,item,onClose}:{mode:'create'|'edit';item?:Department;onClose:()=>void}){
  const [code,setCode]=useState(item?.code||'');const [name,setName]=useState(item?.name||'');
  const submit=(e:React.FormEvent)=>{e.preventDefault();const p={code,name};
    if(mode==='create')router.post(route('hr.referentials.departments.store'),p,{onSuccess:onClose});
    else router.put(route('hr.referentials.departments.update',item!.id),p,{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Département':'✏️ Département'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Code *" value={code} onChange={setCode} required/><Input label="Nom *" value={name} onChange={setName} required/><ModalFooter onClose={onClose}/></form></ModalShell>);
}
function PosteModal({mode,item,allDepartments,onClose}:{mode:'create'|'edit';item?:Position;allDepartments:any[];onClose:()=>void}){
  const [code,setCode]=useState(item?.code||'');const [name,setName]=useState(item?.name||'');const [dept,setDept]=useState(item?.department?.id||'');
  const submit=(e:React.FormEvent)=>{e.preventDefault();const p={code,name,department_id:dept||null};
    if(mode==='create')router.post(route('hr.referentials.positions.store'),p,{onSuccess:onClose});
    else router.put(route('hr.referentials.positions.update',item!.id),p,{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Poste':'️ Poste'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Code *" value={code} onChange={setCode} required/><Input label="Nom *" value={name} onChange={setName} required/>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Département</label><select value={dept} onChange={e=>setDept(e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{allDepartments.map((d:any)=><option key={d.id} value={d.id}>{d.name}</option>)}</select></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}
function TypeModal({mode,item,onClose}:{mode:'create'|'edit';item?:ContractType;onClose:()=>void}){
  const [code,setCode]=useState(item?.code||'');const [name,setName]=useState(item?.name||'');
  const submit=(e:React.FormEvent)=>{e.preventDefault();const p={code,name};
    if(mode==='create')router.post(route('hr.referentials.contract-types.store'),p,{onSuccess:onClose});
    else router.put(route('hr.referentials.contract-types.update',item!.id),p,{onSuccess:onClose});};
  return (<ModalShell title={mode==='create'?'➕ Type':'✏️ Type'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Code *" value={code} onChange={setCode} required/><Input label="Nom *" value={name} onChange={setName} required/><ModalFooter onClose={onClose}/></form></ModalShell>);
}
function EmployeeModal({mode,item,departments,onClose}:{mode:'create'|'edit';item?:Employee;departments:any[];onClose:()=>void}){
  const [form,setForm]=useState({first_name:item?.full_name.split(' ')[0]||'',last_name:item?.full_name.split(' ').slice(1).join(' ')||'',email:item?.email||'',phone:item?.phone||'',sex:item?.sex||'',hire_date:item?.hire_date||new Date().toISOString().slice(0,10),department_id:item?.department?.id||'',position_id:item?.position?.id||''});
  const submit=(e:React.FormEvent)=>{e.preventDefault();
    if(mode==='create')router.post(route('hr.employees.store'),form,{onSuccess:onClose});
    else router.put(route('hr.employees.update',item!.id),form,{onSuccess:onClose});};
  const set=(k:string,v:any)=>setForm(f=>({...f,[k]:v}));
  return (<ModalShell title={mode==='create'?'➕ Employé':'✏️ Employé'} onClose={onClose}><form onSubmit={submit} className="space-y-3">
    <Input label="Prénom *" value={form.first_name} onChange={v=>set('first_name',v)} required/>
    <Input label="Nom *" value={form.last_name} onChange={v=>set('last_name',v)} required/>
    <Input label="Email" value={form.email} onChange={v=>set('email',v)}/>
    <Input label="Téléphone" value={form.phone} onChange={v=>set('phone',v)}/>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Embauche *</label><input type="date" value={form.hire_date} onChange={e=>set('hire_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></div>
    <div><label className="block text-sm font-medium text-gray-700 mb-1">Département</label><select value={form.department_id} onChange={e=>set('department_id',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{departments.map((d:any)=><option key={d.id} value={d.id}>{d.name}</option>)}</select></div>
    <ModalFooter onClose={onClose}/></form></ModalShell>);
}

/* ===== GÉNÉRIQUES ===== */
function ModalShell({title,children,onClose}:{title:string;children:React.ReactNode;onClose:()=>void}){
  return (<div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={onClose}>
    <div className="bg-white rounded-lg shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" onClick={e=>e.stopPropagation()}>
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
