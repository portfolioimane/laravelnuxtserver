<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;
use App\Models\Design;
use App\Models\Team;
use App\Models\Comment;
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable, SpatialTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'name',
        'email', 
        'password',
        'tagline',
        'about',
        'location',
        'formatted_address',
        'available_to_hire'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected $appends=[
        'photo_url'
    ];
    public function getPhotoUrlAttribute(){
     // return 'https://www.gravatar.com/avatar'.md5(strtolower($this->email)).'jpg?d=identicon';
      return 'http://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'?s=200&d=identicon';
        }

    protected $spatialFields = [
        'location',
    ];
   
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

      public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }

    public function designs(){
        return $this->hasMany(Design::class);
    }

    
    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function teams(){
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function ownedTeams(){
        return $this->teams()->where('owner_id', $this->id);
    }

    public function isOwnerOfTeam($team){
        return (bool)$this->teams()
                          ->where('id', $team->id)
                          ->where('owner_id', $this->id)
                          ->count();
    }

    public function invitations(){
        return $this->hasMany(Invitation::class, 'recipient_email', 'email');
    }

    public function chats(){
        return $this->belongsToMany(Chat::class, 'participants');
    }    
    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function getChatWithUser($user_id){
        $chat=$this->chats()->whereHas('participants', function($query) use ($user_id){
            $query->where('user_id', $user_id);
        })
        ->first();
        return $chat;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
