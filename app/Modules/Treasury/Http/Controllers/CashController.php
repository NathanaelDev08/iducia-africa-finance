<?php

namespace App\Modules\Treasury\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Treasury\Models\CashRegister;
use App\Modules\Treasury\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CashController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:cash');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    public function index(Request $request)
    {
        $company = $this->company($request);

        $cashRegisters = CashRegister::where('company_id', $company->id)
            ->withCount('transactions')
            ->orderByDesc('period_start')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'period_start' => $r->period_start->toDateString(),
                'period_end' => $r->period_end->toDateString(),
                'opening_balance' => (float) $r->opening_balance,
                'closing_balance' => (float) $r->closing_balance,
                'status' => $r->status,
                'transactions_count' => $r->transactions_count,
            ]);

        $selectedCashId = $request->query('register') ?? ($cashRegisters->first()['id'] ?? null);
        $selectedCash = $cashRegisters->firstWhere('id', (int) $selectedCashId);

        $cashTransactions = collect();

        if ($selectedCash) {
            $cashTransactions = CashTransaction::where('cash_register_id', $selectedCash['id'])
                ->orderBy('transaction_date')
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'transaction_date' => $t->transaction_date->toDateString(),
                    'reference' => $t->reference,
                    'description' => $t->description,
                    'type' => $t->type,
                    'amount' => (float) $t->amount,
                ]);
        }

        return Inertia::render('Cash/Index', [
            'cashRegisters' => $cashRegisters,
            'selectedCash' => $selectedCash,
            'cashTransactions' => $cashTransactions,
        ]);
    }

    public function storeRegister(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'opening_balance' => 'nullable|numeric',
            'closing_balance' => 'nullable|numeric',
        ]);

        CashRegister::create([
            'company_id' => $this->company($request)->id,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'opening_balance' => $data['opening_balance'] ?? 0,
            'closing_balance' => $data['closing_balance'] ?? 0,
            'status' => 'draft',
        ]);

        return back()->with('success', 'Session de caisse créée.');
    }

    public function destroyRegister(Request $request, CashRegister $register)
    {
        $register->transactions()->delete();
        $register->delete();

        return back()->with('success', 'Session de caisse supprimée.');
    }

    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'transaction_date' => 'required|date',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
        ]);

        CashTransaction::create([
            'cash_register_id' => $data['cash_register_id'],
            'transaction_date' => $data['transaction_date'],
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'status' => 'recorded',
        ]);

        return back()->with('success', 'Transaction de caisse enregistrée.');
    }

    public function destroyTransaction(Request $request, CashTransaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Transaction supprimée.');
    }

    public function importTransactions(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:cash_registers,id',
            'file' => 'required|file|mimes:csv,txt,json',
        ]);

        $register = CashRegister::where('company_id', $this->company($request)->id)
            ->findOrFail($request->input('register_id'));

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $created = 0;
        $errors = 0;

        if ($extension === 'json') {
            $content = json_decode(file_get_contents($file->getRealPath()), true);
            if (!is_array($content)) {
                return back()->with('error', 'Le fichier JSON est invalide.');
            }
            foreach ($content as $row) {
                if (!is_array($row) || !$this->createCashTransaction($register, $row)) {
                    $errors++;
                    continue;
                }
                $created++;
            }
        } else {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    if (!$row) { $errors++; continue; }
                    $payload = [
                        'transaction_date' => $row[0] ?? null,
                        'reference' => $row[1] ?? null,
                        'description' => $row[2] ?? null,
                        'type' => $row[3] ?? null,
                        'amount' => $row[4] ?? null,
                    ];
                    if (!$this->createCashTransaction($register, $payload)) {
                        $errors++;
                        continue;
                    }
                    $created++;
                }
                fclose($handle);
            }
        }

        return back()->with('success', "$created transaction(s) importée(s), $errors erreur(s).");
    }

    public function exportTransactions(Request $request, string $format)
    {
        $company = $this->company($request);
        $registerId = $request->query('register_id');

        $query = CashTransaction::query()->whereIn('cash_register_id', CashRegister::where('company_id', $company->id)->pluck('id'));
        if ($registerId) {
            $query->where('cash_register_id', $registerId);
        }

        $transactions = $query->with('register')->orderBy('transaction_date')->get();

        $rows = $transactions->map(function (CashTransaction $transaction) {
            return [
                $transaction->register->period_start->toDateString(),
                $transaction->register->period_end->toDateString(),
                $transaction->transaction_date->toDateString(),
                $transaction->reference,
                $transaction->description,
                $transaction->type,
                number_format((float) $transaction->amount, 2, '.', ''),
            ];
        })->all();

        if ($format === 'csv') {
            return $this->csvResponse('caisse_' . now()->format('Ymd') . '.csv', [
                'Période début',
                'Période fin',
                'Date',
                'Référence',
                'Description',
                'Type',
                'Montant',
            ], $rows);
        }

        if ($format === 'json') {
            $payload = $transactions->map(fn (CashTransaction $transaction) => [
                'period_start' => $transaction->register->period_start->toDateString(),
                'period_end' => $transaction->register->period_end->toDateString(),
                'transaction_date' => $transaction->transaction_date->toDateString(),
                'reference' => $transaction->reference,
                'description' => $transaction->description,
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
            ]);

            return response()->json($payload, 200, [
                'Content-Disposition' => 'attachment; filename="caisse_' . now()->format('Ymd') . '.json"',
                'Content-Type' => 'application/json',
            ]);
        }

        abort(404);
    }

    protected function createCashTransaction(CashRegister $register, array $row): bool
    {
        $date = trim((string) ($row['transaction_date'] ?? ''));
        $type = Str::lower(trim((string) ($row['type'] ?? '')));
        $amount = isset($row['amount']) ? (float) $row['amount'] : 0;

        if (!$date || $amount <= 0) {
            return false;
        }

        if (in_array($type, ['entrée', 'entree', 'debit'], true)) {
            $type = 'in';
        }

        if (in_array($type, ['sortie', 'credit'], true)) {
            $type = 'out';
        }

        if (!in_array($type, ['in', 'out'], true)) {
            return false;
        }

        $transactionDate = date('Y-m-d', strtotime($date));
        if (!$transactionDate) {
            return false;
        }

        CashTransaction::create([
            'cash_register_id' => $register->id,
            'transaction_date' => $transactionDate,
            'reference' => $row['reference'] ?? null,
            'description' => $row['description'] ?? null,
            'type' => $type,
            'amount' => $amount,
            'status' => 'recorded',
        ]);

        return true;
    }

    protected function csvResponse(string $filename, array $header, array $rows)
    {
        $callback = function () use ($header, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $header, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
