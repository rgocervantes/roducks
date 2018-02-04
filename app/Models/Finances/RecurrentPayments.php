<?php

namespace App\Models\Finances;

use Roducks\Libs\ORM\Model;

class RecurrentPayments extends Model
{

	var $id = "id_payment",
		$fields = [
		'id_card' 		 	=> Model::TYPE_INTEGER,
		'title' 		 	=> Model::TYPE_VARCHAR,
		'day' 		 		=> Model::TYPE_INTEGER,
		'amount' 		 	=> Model::TYPE_DECIMAL,
		'month' 		 	=> Model::TYPE_INTEGER,
		'active'	 		=> Model::TYPE_BOOL,
		'expires_date'		=> Model::TYPE_DATE,
		'created_date'		=> Model::TYPE_DATETIME,
		'updated_date'		=> Model::TYPE_DATETIME
	];

	public function getList($type)
	{

		$this->orderBy(['day' => "ASC"]);

		return 
			$this->filter([
				'active' => 1,
				'type' => $type
			]);
	}

}