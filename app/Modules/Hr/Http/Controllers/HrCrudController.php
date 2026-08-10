<?php
namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\EmployeeDocument;
use App\Modules\Hr\Models\Leave;
use Illuminate\Http\Request;

class HrCrudController extends Controller
{
    protected function company(Request $request): Company
    { return $request->attributes->get('company') ?? Company::first(); }

    /* ===== CONTRATS ===== */
    public function storeContract(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'contract_number' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'base_salary' => 'required|numeric|min:0',
        ]);
        EmployeeContract::create(array_merge($data, [
            'company_id' => $this->company($request)->id,
            'status' => 'active',
        ]));
        return back()->with('success', 'Contrat créé.');
    }

    public function updateContract(Request $request, EmployeeContract $contract)
    {
        $data = $request->validate([
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'base_salary' => 'required|numeric|min:0',
            'status' => 'in:active,expired,terminated',
        ]);
        $contract->update($data);
        return back()->with('success', 'Contrat mis à jour.');
    }

    public function destroyContract(Request $request, EmployeeContract $contract)
    {
        $contract->delete();
        return back()->with('success', 'Contrat supprimé.');
    }

    /* ===== CONGÉS ===== */
    public function storeLeave(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:annual,sick,maternity,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);
        $days = \Carbon\Carbon::parse($data['start_date'])->diffInDays(\Carbon\Carbon::parse($data['end_date'])) + 1;
        Leave::create(array_merge($data, [
            'company_id' => $this->company($request)->id,
            'days_count' => $days,
            'status' => 'pending',
        ]));
        return back()->with('success', 'Demande de congé créée.');
    }

    public function approveLeave(Request $request, Leave $leave)
    {
        $leave->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return back()->with('success', 'Congé approuvé.');
    }

    public function rejectLeave(Request $request, Leave $leave)
    {
        $leave->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return back()->with('success', 'Congé rejeté.');
    }

    public function destroyLeave(Request $request, Leave $leave)
    {
        $leave->delete();
        return back()->with('success', 'Congé supprimé.');
    }

    /* ===== DOCUMENTS (upload) ===== */
    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'document_type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|max:10240',
            'expires_at' => 'nullable|date',
        ]);
        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents/' . $data['employee_id'], 'public');
        }
        EmployeeDocument::create([
            'employee_id' => $data['employee_id'],
            'document_type' => $data['document_type'],
            'name' => $data['name'],
            'file_path' => $path,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => 'valid',
        ]);
        return back()->with('success', 'Document ajouté.');
    }

    public function destroyDocument(Request $request, EmployeeDocument $document)
    {
        $document->delete();
        return back()->with('success', 'Document supprimé.');
    }
}
