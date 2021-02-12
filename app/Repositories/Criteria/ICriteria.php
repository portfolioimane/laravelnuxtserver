<?php
namespace App\Repositories\Criteria;
interface ICriteria{
	public function WithCriteria(...$criteria);
}