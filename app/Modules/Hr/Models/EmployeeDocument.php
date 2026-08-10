<?php
namespace App\Modules\Hr\Models;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = ['company_id','employee_id','document_type','name','file_path','expires_at','status'];
    protected $casts = ['expires_at'=>'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
