import ErpLayout from '@/Layouts/ErpLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
interface Result { group:string; label:string; link:string; }
export default function Search(){
  const [q,setQ]=useState('');const [results,setResults]=useState<Result[]>([]);const [loading,setLoading]=useState(false);
  const inputRef=useRef<HTMLInputElement>(null);
  useEffect(()=>{
    const onKey=(e:KeyboardEvent)=>{ if((e.metaKey||e.ctrlKey)&&e.key.toLowerCase()==='k'){e.preventDefault();inputRef.current?.focus();} };
    window.addEventListener('keydown',onKey); return ()=>window.removeEventListener('keydown',onKey);
  },[]);
  useEffect(()=>{
    if(q.length<2){setResults([]);return;}
    setLoading(true);
    const t=setTimeout(()=>{
      fetch(route('search.json')+'?q='+encodeURIComponent(q),{headers:{Accept:'application/json'}})
        .then(r=>r.json()).then(d=>{setResults(d);setLoading(false);}).catch(()=>setLoading(false));
    },250);
    return ()=>clearTimeout(t);
  },[q]);
  const groups=results.reduce<Record<string,Result[]>>((acc,r)=>{(acc[r.group]=acc[r.group]||[]).push(r);return acc;},{});
  return (<ErpLayout><Head title="Recherche"/>
    <div className="py-6"><div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="mb-6"><h1 className="text-2xl font-bold text-gray-900">🔍 Recherche globale</h1>
      <p className="text-sm text-gray-500 mt-1">Employés, clients, fournisseurs, comptes — raccourci <kbd className="px-1.5 py-0.5 bg-gray-100 border rounded text-xs">Ctrl/⌘ + K</kbd></p></div>
      <input ref={inputRef} autoFocus type="text" value={q} onChange={e=>setQ(e.target.value)} placeholder="Rechercher..." className="w-full rounded-lg border-gray-300 text-base p-3 shadow-sm"/>
      <div className="mt-4 space-y-4">
        {loading&&<p className="text-sm text-gray-500">Recherche...</p>}
        {!loading&&q.length>=2&&results.length===0&&<p className="text-sm text-gray-500">Aucun résultat pour « {q} ».</p>}
        {Object.entries(groups).map(([g,items])=>(
          <div key={g} className="bg-white rounded-lg shadow-sm overflow-hidden">
            <div className="px-4 py-2 bg-gray-50 border-b text-xs font-semibold text-gray-600 uppercase">{g}</div>
            <div className="divide-y">{items.map((r,i)=>(<Link key={i} href={r.link} className="block px-4 py-3 text-sm text-gray-800 hover:bg-indigo-50">{r.label}</Link>))}</div>
          </div>))}
      </div>
    </div></div></ErpLayout>);
}
