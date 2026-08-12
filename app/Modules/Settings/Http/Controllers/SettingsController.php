<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->attributes->get('company') ?? Company::first();

        if (!$company) {
            return redirect()->route('dashboard');
        }

        $tab = $request->get('tab', 'company');

        $menu = [
            ['key' => 'company', 'label' => 'Entreprise', 'icon' => '🏢', 'description' => 'Informations générales'],
            ['key' => 'users', 'label' => 'Utilisateurs', 'icon' => '👥', 'description' => 'Comptes et accès'],
            ['key' => 'taxes', 'label' => 'Fiscalité', 'icon' => '📋', 'description' => 'Taxes et impôts'],
            ['key' => 'payroll', 'label' => 'Paie', 'icon' => '💰', 'description' => 'Cotisations et salaires'],
            ['key' => 'accounting', 'label' => 'Comptabilité', 'icon' => '📒', 'description' => 'Journaux et comptes'],
            ['key' => 'general', 'label' => 'Général', 'icon' => '⚙️', 'description' => 'Préférences système'],
            ['key' => 'user-management', 'label' => 'Gestion utilisateurs', 'icon' => '🛡️', 'description' => 'Comptes et modules'],
        ];

        $data = [
            'company' => $company,
            'tab' => $tab,
            'menu' => $menu,
        ];

        if ($tab === 'users') {
            $data['users'] = $company->users()
                ->withPivot(['role', 'is_active'])
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.last_seen_at', 'users.created_at']);
        }

        if ($tab === 'taxes') {
            $data['taxes'] = DB::table('taxes')
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get();
        }

        if ($tab === 'payroll') {
            $data['contributions'] = DB::table('social_contributions')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            $data['pay_items'] = DB::table('pay_items')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();
        }

        if ($tab === 'accounting') {
            $data['journals'] = DB::table('journals')
                ->where('company_id', $company->id)
                ->orderBy('code')
                ->get();
            $data['charts'] = DB::table('chart_accounts')
                ->where('company_id', $company->id)
                ->get();
        }

        if ($tab === 'general') {
            $data['settings'] = Setting::where('company_id', $company->id)
                ->get()
                ->keyBy('key');
        }

        if ($tab === 'user-management') {
            $data['users'] = $company->users()
                ->with(['modules'])
                ->withPivot(['role', 'is_active'])
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email', 'users.email_verified_at', 'users.last_seen_at', 'users.created_at']);
            $data['modules'] = \App\Models\SystemModule::where('is_active', true)->orderBy('display_order')->get();
        }

        return Inertia::render('Settings/Index', $data);
    }

    public function updateCompany(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'rccm' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
        ]);

        $company->update($validated);

        return back()->with('success', 'Informations entreprise mises à jour.');
    }

    public function updateSettings(Request $request, Company $company)
    {
        $validated = $request->validate([
            'language' => 'required|in:fr,en',
            'timezone' => 'required|string',
            'invoice_payment_days' => 'required|integer|min:0|max:90',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['company_id' => $company->id, 'key' => $key],
                ['value' => $value, 'group' => 'general']
            );
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }

    // ═══════════ CRUD FISCALITÉ ═══════════
    public function storeTax(Request $request)
    {
        $v = $request->validate(['name'=>'required|string|max:100','code'=>'required|string|max:20','type'=>'required|in:vat,other']);
        $company = $request->attributes->get('company') ?? Company::first();
        DB::table('taxes')->insert(['company_id'=>$company->id,'name'=>$v['name'],'code'=>$v['code'],'type'=>$v['type'],'scope'=>'both','is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Taxe ajoutée.');
    }

    public function updateTax(Request $request, $id)
    {
        $v = $request->validate(['name'=>'required|string|max:100','code'=>'required|string|max:20','type'=>'required|in:vat,other']);
        DB::table('taxes')->where('id',$id)->update(['name'=>$v['name'],'code'=>$v['code'],'type'=>$v['type'],'updated_at'=>now()]);
        return back()->with('success', 'Taxe mise à jour.');
    }

    public function destroyTax($id)
    {
        DB::table('taxes')->where('id',$id)->delete();
        return back()->with('success', 'Taxe supprimée.');
    }

    // ═══════════ CRUD PAIE ═══════════
    public function storeContribution(Request $request)
    {
        $v = $request->validate(['name'=>'required|string|max:100','code'=>'required|string|max:20','organism'=>'nullable|string|max:100']);
        DB::table('social_contributions')->insert(['code'=>$v['code'],'name'=>$v['name'],'organism'=>$v['organism'] ?? 'CNPS','is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Cotisation ajoutée.');
    }

    public function storePayItem(Request $request)
    {
        $v = $request->validate(['name'=>'required|string|max:100','code'=>'required|string|max:20','type'=>'required|in:earning,deduction']);
        $company = $request->attributes->get('company') ?? Company::first();
        DB::table('pay_items')->insert(['company_id'=>$company->id,'code'=>$v['code'],'name'=>$v['name'],'type'=>$v['type'],'calculation_method'=>'fixed','base_type'=>'gross','is_taxable'=>false,'is_subject_to_contributions'=>false,'is_visible_on_payslip'=>true,'display_order'=>99,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Rubrique de paie ajoutée.');
    }

    // ═══════════ CRUD COMPTABILITÉ ═══════════
    public function storeJournal(Request $request)
    {
        $v = $request->validate(['code'=>'required|string|max:10','name'=>'required|string|max:100','type'=>'required|string|max:20']);
        $company = $request->attributes->get('company') ?? Company::first();
        DB::table('journals')->insert(['company_id'=>$company->id,'code'=>$v['code'],'name'=>$v['name'],'type'=>$v['type'],'next_number'=>1,'is_active'=>true,'requires_attachment'=>false,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success', 'Journal ajouté.');
    }

    // ═══════════ STATUT UTILISATEUR ═══════════
    public function toggleUser(\App\Models\User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', $user->is_active ? 'Compte réactivé.' : 'Compte désactivé.');
    }
}
