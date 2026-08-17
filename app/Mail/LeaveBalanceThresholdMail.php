<?php
namespace App\Mail;
use App\Modules\Hr\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class LeaveBalanceThresholdMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Employee $employee, public float $balance, public bool $forHr = false) {}
    public function build()
    {
        return $this->subject('FIDUCIA ERP - Solde de congés élevé')->view('emails.leave-balance-threshold');
    }
}
