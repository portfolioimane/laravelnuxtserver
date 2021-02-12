<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Chat extends Model
{
    public function participants(){
    	return $this->belongsToMany(User::class, 'participants');
    }
    public function messages(){
    	return $this->hasMany(Message::class);
    }
    public function getLatestMessageAttribute(){
    	return $this->messages()->latest()->first();
    }
    public function isUnreadForUser($userId){
    	return (bool)$this->messages()
    	        ->whereNull('last_read')
    	        ->where('user_id', '<>', $userId)
    	        ->count();
    }
    public function markAsReadForUser($userId){
    	return (bool)$this->messages()
    	        ->whereNull('last_read')
    	        ->where('user_id', '<>', $userId)
    	        ->update([
                    'last_read'=> Carbon::now()
    	        ]);
    }
}
