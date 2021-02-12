<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invitation;

class SendInvitationToJoinTeam extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $invitations;
    public $user_exists;
    public function __construct(Invitation $invitation, bool $user_exists)
    {
        $this->invitation=$invitation;
        $this->user_exists=$user_exists;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->user_exists){
             $url=config('app.client_url').'/settings/teams';
             return $this->markdown('emails.invitations.invite-existing-user')
                    ->subject('Invitation to join team' . $this->invitation->team->name)
                    ->with([
                      'invitation'=> $this->invitation,
                      'url'=>$url   
                    ]);
        }else{
             $url=config('app.client_url').'/register?invitation='.$this->invitation->recipient_email;
             return $this->markdown('emails.invitations.invite-new-user')
                    ->subject('Invitation to join team' . $this->invitation->team->name)
                    ->with([
                      'invitation'=> $this->invitation,
                      'url'=>$url   
                    ]);
        }
       
    }
}
