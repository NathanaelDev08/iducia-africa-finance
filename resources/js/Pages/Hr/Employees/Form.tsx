import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Dept { id:number; name:string; }
interface Pos { id:number; name:string; department_id:number|null; }
interface Props { employee:any|null; departments:Dept[]; positions:Pos[]; contractTypes:any[]; }

const emptyForm = {
  first_name:'', last_name:'', sex:'', birth_date:'', birth_place:'', nationality:'',
  id_card_number:'', cnps_number:'', email:'', phone:'', address:'', marital_status:'',
  dependents_count:0, hire_date:new Date().toISOString().slice(0,10), seniority_date:'',
  department_id:'', position_id:'', payment_method:'bank', bank_name:'', bank_account:'',
  mobile_money:'', status:'active',
};

export default function Form({employee,departments,positions}:Props){
  const isEdit = !!employee;
  const [form,setForm]=useState<any>(isEdit ? {
    first_name:employee.first_name||'', last_name:employee.last_name||'', sex:employee.sex||'',
    birth_date:employee.birth_date||'', birth_place:employee.birth_place||'', nationality:employee.nationality||'',
    id_card_number:employee.id_card_number||'', cnps_number:employee.cnps_number||'', email:employee.email||'',
    phone:employee.phone||'', address:employee.address||'', marital_status:employee.marital_status||'',
    dependents_count:employee.dependents_count??0, hire_date:employee.hire_date||'', seniority_date:employee.seniority_date||'',
    department_id:employee.department_id||'', position_id:employee.position_id||'', payment_method:employee.payment_method||'bank',
    bank_name:employee.bank_name||'', bank_account:employee.bank_account||'', mobile_money:employee.mobile_money||'', status:employee.status||'active',
  } : emptyForm);
  const [errors,setErrors]=useState<any>({});
  const [processing,setProcessing]=useState(false);
  const set=(k:string,v:any)=>setForm((f:any)=>({...f,[k]:v}));
  const deptPositions = positions.filter(p=>!form.department_id || p.department_id===+form.department_id);

  const submit=(e:React.FormEvent)=>{
    e.preventDefault(); setProcessing(true); setErrors({});
    const payload={...form, department_id:form.department_id||null, position_id:form.position_id||null,
      seniority_date:form.seniority_date||null, birth_date:form.birth_date||null};
    const opts={ onError:(er:any)=>{setErrors(er);setProcessing(false);}, onFinish:()=>setProcessing(false) };
    if(isEdit) router.put(route('hr.employees.update',employee.id),payload,opts);
    else router.post(route('hr.employees.store'),payload,opts);
  };

  return (
    <ErpLayout>
      <Head title={isEdit?'Modifier employé':'Nouvel employé'}/>
      <div className="py-6"><div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <Link href={route('hr.index',{tab:'employes'})} className="text-sm text-indigo-600 hover:underline">← Retour</Link>
        <div className="mt-2 mb-6"><h1 className="text-2xl font-bold text-gray-900">{isEdit?'✏️ Modifier '+employee.full_name:'➕ Nouvel employé'}</h1>
        {isEdit&&<p className="text-sm text-gray-500 font-mono mt-1">{employee.matricule}</p>}</div>

        <form onSubmit={submit} className="space-y-6">
          {/* IDENTITÉ */}
          <Section title="👤 Identité">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Field label="Prénom *" error={errors.first_name}><input value={form.first_name} onChange={e=>set('first_name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
              <Field label="Nom *" error={errors.last_name}><input value={form.last_name} onChange={e=>set('last_name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
              <Field label="Sexe"><select value={form.sex} onChange={e=>set('sex',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option><option value="M">Masculin</option><option value="F">Féminin</option></select></Field>
              <Field label="Date de naissance" error={errors.birth_date}><input type="date" value={form.birth_date} onChange={e=>set('birth_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Lieu de naissance"><input value={form.birth_place} onChange={e=>set('birth_place',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Nationalité"><input value={form.nationality} onChange={e=>set('nationality',e.target.value)} placeholder="Ivoirienne" className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="N° pièce d'identité (CNI)"><input value={form.id_card_number} onChange={e=>set('id_card_number',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="N° CNPS"><input value={form.cnps_number} onChange={e=>set('cnps_number',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
            </div>
          </Section>

          {/* CONTACT */}
          <Section title="📞 Contact & situation">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Field label="Email" error={errors.email}><input type="email" value={form.email} onChange={e=>set('email',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Téléphone"><input value={form.phone} onChange={e=>set('phone',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Situation matrimoniale"><select value={form.marital_status} onChange={e=>set('marital_status',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option><option value="single">Célibataire</option><option value="married">Marié(e)</option><option value="divorced">Divorcé(e)</option><option value="widowed">Veuf(ve)</option></select></Field>
              <Field label="Nombre d'enfants" error={errors.dependents_count}><input type="number" min="0" value={form.dependents_count} onChange={e=>set('dependents_count',+e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <div className="md:col-span-2"><Field label="Adresse"><textarea value={form.address} onChange={e=>set('address',e.target.value)} rows={2} className="w-full rounded-md border-gray-300 text-sm"/></Field></div>
            </div>
          </Section>

          {/* POSTE */}
          <Section title="💼 Poste & embauche">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Field label="Date d'embauche *" error={errors.hire_date}><input type="date" value={form.hire_date} onChange={e=>set('hire_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm" required/></Field>
              <Field label="Date d'ancienneté"><input type="date" value={form.seniority_date} onChange={e=>set('seniority_date',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Statut"><select value={form.status} onChange={e=>set('status',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="active">Actif</option><option value="inactive">Inactif</option><option value="suspended">Suspendu</option><option value="terminated">Terminé</option></select></Field>
              <Field label="Département" error={errors.department_id}><select value={form.department_id} onChange={e=>{set('department_id',e.target.value);set('position_id','');}} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{departments.map(d=><option key={d.id} value={d.id}>{d.name}</option>)}</select></Field>
              <Field label="Poste" error={errors.position_id}><select value={form.position_id} onChange={e=>set('position_id',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="">—</option>{deptPositions.map(p=><option key={p.id} value={p.id}>{p.name}</option>)}</select></Field>
            </div>
          </Section>

          {/* PAIEMENT */}
          <Section title="💳 Coordonnées de paiement">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Field label="Mode de paiement"><select value={form.payment_method} onChange={e=>set('payment_method',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"><option value="bank">Virement bancaire</option><option value="mobile_money">Mobile Money</option><option value="cash">Espèces</option></select></Field>
              <Field label="Banque"><input value={form.bank_name} onChange={e=>set('bank_name',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="N° compte bancaire"><input value={form.bank_account} onChange={e=>set('bank_account',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
              <Field label="Mobile Money"><input value={form.mobile_money} onChange={e=>set('mobile_money',e.target.value)} className="w-full rounded-md border-gray-300 text-sm"/></Field>
            </div>
          </Section>

          <div className="flex justify-end gap-3">
            <Link href={route('hr.index',{tab:'employes'})} className="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Annuler</Link>
            <button type="submit" disabled={processing} className="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50">
              {processing?'Enregistrement...':(isEdit?'Enregistrer les modifications':'Créer l\'employé')}
            </button>
          </div>
        </form>
      </div></div>
    </ErpLayout>
  );
}

function Section({title,children}:{title:string;children:React.ReactNode}){
  return (<div className="bg-white rounded-lg shadow-sm p-6">
    <h3 className="font-bold text-gray-800 mb-4 border-b pb-2">{title}</h3>{children}</div>);
}
function Field({label,error,children}:{label:string;error?:string;children:React.ReactNode}){
  return (<div><label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>{children}{error&&<p className="text-xs text-red-600 mt-1">{error}</p>}</div>);
}
