<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\IInvitation;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Models\Team;
use Mail;
use App\Mail\SendInvitationToJoinTeam;
class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
	public function __construct(IInvitation $invitations, ITeam $teams, IUser $users){
		$this->invitations=$invitations;
		$this->teams=$teams;
		$this->users=$users;
	}
	public function invite(Request $request, $teamId){
         $team=$this->teams->find($teamId);
         $this->validate($request, [
                'email'=>['required', 'email']
         ]);  
         $user=auth()->user();
         if(! $user->isOwnerOfTeam($team)){
         	return response()->json([
              'email'=>'You are not the team owner'
         	], 401);         	
         }
         if($team->hasPendingInvite($request->email)){
         	return response()->json([
                'email'=>'Email already has a pending invitation'
         	], 422);
         }
        $recipient=$this->users->findByEmail($request->email);
        if(! $recipient){
        	$this->createInvitation(false, $team, $request->email);
        	return response()->json([
              'message'=>'Invitation sent to user'
        	], 200);
        }
        if($team->hasUser($recipient)){
        	return response()->json([
               'email'=>'this user seems to be a team member already'
        	], 422);
        }
        $this->createInvitation(true, $team, $request->email);
        return response()->json([
              'message'=>'Invitation sent to user'
        	], 200);
	}
	protected function createInvitation(bool $user_exists, Team $team, string $email){
		$invitation=$this->invitations->create([
                 'team_id'=>$team->id,
                 'sender_id'=>auth()->id(),
                 'recipient_email'=>$email,
                 'token'=>md5(uniqid(microtime()))
        	]);
        	Mail::to($email)
        	  ->send(new sendInvitationToJoinTeam($invitation, $user_exists));
	}
	public function resend($id){
         $invitation=$this->invitations->find($id);
          $this->authorize('resend', $invitation);
         $recipient=$this->users->findByEmail($invitation->recipient_email);
         Mail::to($invitation->recipient_email)
              ->send(new sendInvitationToJoinTeam($invitation, !is_null($recipient)));
              return response()->json(['message'=>'Invitation resent'], 200);
	}
	public function respond(Request $request, $id){
            $this->validate($request, [
               'token'=>['required'],
               'decision'=>['required']
            ]);
            $token=$request->token;
            $decision=$request->decision;
            $invitation=$this->invitations->find($id);
             $this->authorize('respond', $invitation);
            if($invitation->token !== $token){
                return response()->json([
                  'message'=>'Invalide Token'
                ], 401);
            }
            if($decision !== 'deny'){
               $this->invitations->addUserToTeam($invitation->team, auth()->id());
            }
            $invitation->delete();
            return response()->json(['message'=>'Successfull'], 200);
	       }

	   public function destroy($id){
            $invitation=$this->invitations->find($id);
            $this->authorize('delete', $invitation);
            $invitation->delete();
            return response()->json(['message'=>'deleted'], 200);
    }
}
