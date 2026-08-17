<?php
namespace App\Modules\Hr\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContract extends Model
{
    protected $fillable = ['company_id','employee_id','contract_type_id','contract_number','start_date','end_date','base_salary','salaire_categoriel','sursalaire','has_cmu','has_cnps','status','notes'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','base_salary'=>'decimal:2','salaire_categoriel'=>'decimal:2','sursalaire'=>'decimal:2','has_cmu'=>'boolean','has_cnps'=>'boolean'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function contractType(): BelongsTo { return $this->belongsTo(ContractType::class, 'contract_type_id'); }
}
