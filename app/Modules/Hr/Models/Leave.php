<?php
namespace App\Modules\Hr\Models;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = ['company_id','employee_id','leave_type','start_date','end_date','days_count','reason','status','approved_by','approved_at'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','approved_at'=>'datetime'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
