<?php

namespace App\Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Services\EntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingEntryController extends Controller
{
    public function __construct(protected EntryService $entryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $company = app('currentCompany');

        $entries = AccountingEntry::where('company_id', $company->id)
            ->with(['journal', 'period', 'lines.account', 'validatedBy'])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'period_id' => 'required|exists:periods,id',
            'reference' => 'nullable|string|max:100',
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.description' => 'nullable|string|max:255',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        $company = app('currentCompany');
        $entry = $this->entryService->createDraft($company, $data, $data['lines']);

        return response()->json($entry->load('lines.account'), 201);
    }

    public function validate_entry(AccountingEntry $entry): JsonResponse
    {
        $this->authorizeCompany($entry);

        $validated = $this->entryService->validate($entry, $request->user());

        return response()->json($validated);
    }

    public function reverse(Request $request, AccountingEntry $entry): JsonResponse
    {
        $this->authorizeCompany($entry);

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reversal = $this->entryService->reverse($entry, $request->user(), $data['reason']);

        return response()->json([
            'message' => 'Écriture contre-passée avec succès.',
            'reversal' => $reversal->load('lines.account'),
        ]);
    }

    protected function authorizeCompany(AccountingEntry $entry): void
    {
        $company = app('currentCompany');

        if ($entry->company_id !== $company->id) {
            abort(403, 'Cette écriture n\'appartient pas à l\'entreprise active.');
        }
    }
}
