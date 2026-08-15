import ErpLayout from '@/Layouts/ErpLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
function UploadCard({title,desc,routeName,format}:{title:string;desc:string;routeName:string;format:string}){
  const [file,setFile]=useState<File|null>(null);
  const submit=(e:React.FormEvent)=>{e.preventDefault();if(!file)return;const fd=new FormData();fd.append('file',file);router.post(route(routeName),fd);};
  return (<div className="bg-white rounded-lg shadow-sm p-6">
    <h3 className="font-bold text-gray-800 mb-2">{title}</h3><p className="text-sm text-gray-500 mb-3">{desc}</p>
    <p className="text-xs text-gray-400 mb-3 font-mono">Format : {format}</p>
    <form onSubmit={submit} className="space-y-3">
      <input type="file" accept=".csv,.txt" onChange={e=>setFile(e.target.files?.[0]||null)} className="w-full text-sm"/>
      <button type="submit" disabled={!file} className="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm py-2 px-4 rounded-md">⬆ Importer</button>
    </form></div>);
}
export default function Import(){
  return (<ErpLayout><Head title="Imports"/>
    <div className="py-6"><div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">⬆ Imports en masse</h1><p className="text-sm text-gray-500 mt-1">Chargez vos données via CSV</p></div>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <UploadCard title="👥 Employés" desc="Importez plusieurs employés d'un coup." routeName="import.employees" format="first_name,last_name,email,phone,hire_date"/>
        <UploadCard title="📒 Écritures comptables" desc="Importez des écritures (regroupées par référence)." routeName="import.journal" format="entry_date,journal_code,reference,description,account_number,debit,credit"/>
      </div>
    </div></div></ErpLayout>);
}
