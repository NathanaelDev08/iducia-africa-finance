import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link } from '@inertiajs/react';
interface Alert { type:string; severity:string; message:string; link:string; }
const SEV: Record<string,{label:string;color:string}> = { high:{label:'Urgent',color:'bg-red-100 text-red-800'}, medium:{label:'À suivre',color:'bg-yellow-100 text-yellow-800'}, low:{label:'Info',color:'bg-blue-100 text-blue-800'} };
export default function Notifications({alerts,high_count}:{alerts:Alert[];high_count:number}){
  return (<ErpLayout><Head title="Notifications"/>
    <div className="py-6"><div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🔔 Notifications & alertes</h1>
      <p className="text-sm text-gray-500 mt-1">{high_count>0?high_count+' alerte(s) urgente(s)':'Aucune alerte urgente'} · {alerts.length} notification(s)</p></div>
      <div className="space-y-3">
        {alerts.length===0&&<div className="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">✅ Aucune alerte. Tout est à jour !</div>}
        {alerts.map((a,i)=>{const s=SEV[a.severity]||SEV.low;return(
          <Link key={i} href={a.link} className="block bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition flex items-center gap-4">
            <span className={'px-2 py-1 rounded-full text-xs font-semibold shrink-0 '+s.color}>{s.label}</span>
            <span className="text-sm text-gray-800">{a.message}</span>
            <span className="ml-auto text-indigo-600 text-sm">→</span>
          </Link>);})}
      </div>
    </div></div></ErpLayout>);
}
