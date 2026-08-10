<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Modules\Settings\Models\SequenceNumber;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Models\Tax;
use App\Modules\System\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        try {
            $users = User::with('companies')->orderBy('name')->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'companies' => $u->companies->pluck('id')->values(),
            ]);
        } catch (\Throwable $e) {
            $users = User::orderBy('name')->get()->map(fn ($u) => [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'companies' => [],
            ]);
        }

        try {
            $rates = ExchangeRate::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))
                ->orderByDesc('effective_from')
                ->get();
        } catch (\Throwable $e) {
            $rates = collect();
        }
        return Inertia::render('Settings/Index', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug ?? '',
                'tax_number' => $company->tax_number ?? '',
                'currency' => $company->currency ?? 'XOF',
                'fiscal_year_start_month' => $company->fiscal_year_start_month ?? 1,
            ],
            'general' => Setting::where('company_id', $company->id)->pluck('value', 'key'),
            'taxes' => Tax::where('company_id', $company->id)->orderBy('code')->get(),
            'sequences' => SequenceNumber::where('company_id', $company->id)->orderBy('code')->get(),
            'rates' => $rates,
            'users' => $users,
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'initialTab' => $request->query('tab', 'general'),
        ]);
    }

    // ═══════════ ENTREPRISE & GÉNÉRAL ═══════════
    public function updateGeneral(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'fiscal_year_start_month' => 'nullable|integer|min:1|max:12',
            'settings' => 'nullable|array',
        ]);

        $fields = [
            'name' => $data['name'],
            'tax_number' => $data['tax_number'] ?? $company->tax_number,
            'currency' => $data['currency'] ?? $company->currency,
            'fiscal_year_start_month' => $data['fiscal_year_start_month'] ?? $company->fiscal_year_start_month,
        ];

        try {
            DB::table('companies')->where('id', $company->id)->update($fields);
        } catch (\Throwable $e) {
            try { $company->update($fields); } catch (\Throwable $e2) {}
        }

        foreach (($data['settings'] ?? []) as $key => $value) {
            if (!is_string($key)) continue;
            Setting::updateOrCreate(
                ['company_id' => $company->id, 'key' => $key],
                ['value' => (string) $value, 'group' => 'general']
            );
        }

        return back()->with('success', 'Paramètres généraux enregistrés.');
    }

    // ═══════════ TAXES ═══════════
    public function storeTax(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('taxes')->where('company_id', $company->id)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:vat,withholding,income_tax,other',
            'rate' => 'required|numeric|min:0|max:100',
            'account_number' => 'nullable|string|max:20',
            'effective_from' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $data['company_id'] = $company->id;
        Tax::create($data);

        return back()->with('success', 'Taxe ajoutée.');
    }

    public function updateTax(Request $request, Tax $tax)
    {
        $company = $this->company($request);
        if ($tax->company_id !== $company->id) abort(403);

        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:vat,withholding,income_tax,other',
            'rate' => 'required|numeric|min:0|max:100',
            'account_number' => 'nullable|string|max:20',
            'effective_from' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $tax->update($data);

        return back()->with('success', 'Taxe modifiée.');
    }

    public function destroyTax(Request $request, Tax $tax)
    {
        $company = $this->company($request);
        if ($tax->company_id !== $company->id) abort(403);

        $tax->delete();

        return back()->with('success', 'Taxe supprimée.');
    }

    // ═══════════ SÉQUENCES ═══════════
    public function updateSequence(Request $request, SequenceNumber $sequence)
    {
        $company = $this->company($request);
        if ($sequence->company_id !== $company->id) abort(403);

        $data = $request->validate([
            'prefix' => 'nullable|string|max:20',
            'next_number' => 'required|integer|min:1',
            'format' => 'required|string|max:60',
        ]);

        $sequence->update($data);

        return back()->with('success', 'Séquence mise à jour.');
    }

    // ═══════════ UTILISATEURS ═══════════
    public function storeUser(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'companies' => 'nullable|array',
            'companies.*' => 'integer|exists:companies,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $companyIds = $data['companies'] ?? [$company->id];
        foreach ($companyIds as $cid) {
            try { $user->companies()->syncWithoutDetaching([$cid => ['role' => 'admin']]); }
            catch (\Throwable $e) { $user->companies()->syncWithoutDetaching([$cid]); }
        }

        return back()->with('success', "Utilisateur {$user->name} créé.");
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'companies' => 'nullable|array',
            'companies.*' => 'integer|exists:companies,id',
        ]);

        $fields = ['name' => $data['name'], 'email' => $data['email']];
        if (!empty($data['password'])) {
            $fields['password'] = Hash::make($data['password']);
        }
        $user->update($fields);

        if (isset($data['companies'])) {
            $sync = [];
            foreach ($data['companies'] as $cid) { $sync[$cid] = ['role' => 'admin']; }
            try { $user->companies()->sync($sync); }
            catch (\Throwable $e) { $user->companies()->sync(array_keys($sync)); }
        }

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    // ═══════════ DEVISES (taux de change) ═══════════
    public function storeRate(Request $request)
    {
        $company = $this->company($request);

        $data = $request->validate([
            'currency_code' => 'required|string|max:10',
            'currency_name' => 'nullable|string|max:100',
            'rate_to_base' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        try {
            ExchangeRate::create($data + ['company_id' => $company->id, 'is_active' => true]);
        } catch (\Throwable $e) {
            DB::table('exchange_rates')->insert($data + [
                'company_id' => $company->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Taux de change ajouté.');
    }

    public function updateRate(Request $request, ExchangeRate $rate)
    {
        $data = $request->validate([
            'currency_code' => 'required|string|max:10',
            'currency_name' => 'nullable|string|max:100',
            'rate_to_base' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $rate->update($data);

        return back()->with('success', 'Taux de change modifié.');
    }

    public function destroyRate(Request $request, ExchangeRate $rate)
    {
        $rate->delete();

        return back()->with('success', 'Taux de change supprimé.');
    }

    // ═══════════ IMPORTS CSV ═══════════
    public function importEmployees(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $company = $this->company($request);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        if (count($rows) < 2) return back()->with('error', 'Fichier vide ou sans données.');

        $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows));

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < 2) { $skipped++; continue; }
            $data = array_combine($header, array_pad($row, count($header), null));

            try {
                \App\Modules\Hr\Models\Employee::create([
                    'company_id' => $company->id,
                    'matricule' => 'IMP-' . now()->format('ymd') . '-' . str_pad((string) ($created + 1), 4, '0', STR_PAD_LEFT),
                    'first_name' => trim($data['first_name'] ?? ''),
                    'last_name' => trim($data['last_name'] ?? ''),
                    'email' => trim($data['email'] ?? ''),
                    'phone' => trim($data['phone'] ?? ''),
                    'hire_date' => trim($data['hire_date'] ?? '') ?: now()->toDateString(),
                    'status' => 'active',
                ]);
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        return back()->with('success', "{$created} employé(s) importé(s)" . ($skipped ? ", {$skipped} ligne(s) ignorée(s)" : '') . '.');
    }

    public function importJournal(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $company = $this->company($request);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        if (count($rows) < 2) return back()->with('error', 'Fichier vide ou sans données.');

        $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows));

        // Regrouper les lignes par référence (1 référence = 1 écriture)
        $entries = [];
        foreach ($rows as $row) {
            if (count($row) < 4) continue;
            $data = array_combine($header, array_pad($row, count($header), null));
            $ref = trim($data['reference'] ?? '') ?: 'IMPORT-' . now()->format('YmdHis');
            $entries[$ref][] = $data;
        }

        $created = 0;
        $failed = 0;

        foreach ($entries as $ref => $lines) {
            try {
                $entry = \App\Modules\Accounting\Models\JournalEntry::create([
                    'company_id' => $company->id,
                    'entry_date' => trim($lines[0]['entry_date'] ?? '') ?: now()->toDateString(),
                    'journal_code' => strtoupper(trim($lines[0]['journal_code'] ?? 'OD')),
                    'reference' => $ref,
                    'description' => trim($lines[0]['description'] ?? 'Import CSV'),
                ]);

                foreach ($lines as $line) {
                    \App\Modules\Accounting\Models\JournalItem::create([
                        'entry_id' => $entry->id,
                        'account_number' => trim($line['account_number'] ?? ''),
                        'debit' => (float) ($line['debit'] ?? 0),
                        'credit' => (float) ($line['credit'] ?? 0),
                    ]);
                }

                $created++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return back()->with('success', "{$created} écriture(s) importée(s)" . ($failed ? ", {$failed} échouée(s)" : '') . '.');
    }
}
