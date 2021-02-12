<?php
namespace App\Repositories\Eloquent;
use App\Repositories\Contracts\IMessage;
use App\Models\Message;
use App\Repositories\Eloquent\BaseRepository;
class MessageRepository extends BaseRepository implements IMessage{
	public function model(){
		return Message::class;
	}
}