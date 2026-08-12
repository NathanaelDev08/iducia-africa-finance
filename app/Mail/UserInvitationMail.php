<?php
namespace App\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public User $user, public string $tempPassword, public string $invitedBy) {}
    public function build()
    {
        return $this->subject('FIDUCIA ERP - Vos identifiants')->view('emails.user-invitation');
    }
}
