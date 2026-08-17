<?php
namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeChild;
use App\Modules\Hr\Models\EmployeeContract;
use App\Modules\Hr\Models\EmployeeDocument;
use App\Modules\Hr\Models\Leave;
use Illuminate\Http\Request;

class HrCrudController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:hr');
    }

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
            'salaire_categoriel' => 'nullable|numeric|min:0',
            'sursalaire' => 'nullable|numeric|min:0',
            'has_cmu' => 'nullable|boolean',
            'has_cnps' => 'nullable|boolean',
        ]);
        $data['has_cmu'] = $request->boolean('has_cmu');
        $data['has_cnps'] = $request->has('has_cnps') ? $request->boolean('has_cnps') : true;
        EmployeeContract::create(array_merge($data, [
            'company_id' => $this->company($request)->id,
            'status' => 'active',
        ]));
        return back()->with('success', 'Contrat créé.');
    }

    public function updateContract(Request $request, EmployeeContract $contract)
    {
        if ($contract->company_id !== $this->company($request)->id) abort(403);
        $data = $request->validate([
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'base_salary' => 'required|numeric|min:0',
            'salaire_categoriel' => 'nullable|numeric|min:0',
            'sursalaire' => 'nullable|numeric|min:0',
            'has_cmu' => 'nullable|boolean',
            'has_cnps' => 'nullable|boolean',
            'status' => 'in:active,expired,terminated',
        ]);
        $data['has_cmu'] = $request->boolean('has_cmu');
        $data['has_cnps'] = $request->has('has_cnps') ? $request->boolean('has_cnps') : true;
        $contract->update($data);
        return back()->with('success', 'Contrat mis à jour.');
    }

    public function destroyContract(Request $request, EmployeeContract $contract)
    {
        if ($contract->company_id !== $this->company($request)->id) abort(403);
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
        if ($leave->company_id !== $this->company($request)->id) abort(403);
        $leave->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        // Le congé pris peut faire repasser le solde acquis sous le seuil d'alerte :
        // on réarme l'alerte pour qu'un futur franchissement du seuil déclenche un nouvel email.
        $employee = $leave->employee;
        if ($employee && $employee->leave_alert_sent_at && $employee->accruedLeaveBalance() < Employee::LEAVE_BALANCE_ALERT_THRESHOLD) {
            $employee->forceFill(['leave_alert_sent_at' => null])->save();
        }

        return back()->with('success', 'Congé approuvé.');
    }

    public function rejectLeave(Request $request, Leave $leave)
    {
        if ($leave->company_id !== $this->company($request)->id) abort(403);
        $leave->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return back()->with('success', 'Congé rejeté.');
    }

    public function destroyLeave(Request $request, Leave $leave)
    {
        if ($leave->company_id !== $this->company($request)->id) abort(403);
        $leave->delete();
        return back()->with('success', 'Congé supprimé.');
    }

    /* ===== DOCUMENTS (upload) ===== */
    public function storeDocument(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where('company_id', $company->id)],
            'document_type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'expires_at' => 'nullable|date',
        ]);
        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents/' . $company->id . '/' . $data['employee_id'], 'public');
        }
        EmployeeDocument::create([
            'company_id' => $company->id,
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
        if ($document->company_id !== $this->company($request)->id) abort(403);
        $document->delete();
        return back()->with('success', 'Document supprimé.');
    }

    /* ===== ENFANTS ===== */
    public function storeChild(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'employee_id' => ['required', \Illuminate\Validation\Rule::exists('employees', 'id')->where('company_id', $company->id)],
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
        ]);
        EmployeeChild::create($data);
        return back()->with('success', 'Enfant ajouté.');
    }

    public function destroyChild(Request $request, EmployeeChild $child)
    {
        if ($child->employee->company_id !== $this->company($request)->id) abort(403);
        $child->delete();
        return back()->with('success', 'Enfant supprimé.');
    }
}
