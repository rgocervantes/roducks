<?php

/**
 *	@package FinancesModel
 *
 */

namespace DB\Models\Finances;

use Model;

class cards extends Model {
	
	var $id = "id_card";
	var $fields = [
		'id_bank' 		 	=> Model::TYPE_INTEGER,
		'type' 		 		=> Model::TYPE_BOOL,
		'cut_day' 	 		=> Model::TYPE_INTEGER,
		'payment_day' 		=> Model::TYPE_INTEGER,
		'title' 			=> Model::TYPE_VARCHAR,
		'card_number' 		=> Model::TYPE_INTEGER,
		'month'	 			=> Model::TYPE_INTEGER,
		'active'	 		=> Model::TYPE_BOOL,
		'created_date'		=> Model::TYPE_DATETIME,			
		'updated_date'		=> Model::TYPE_DATETIME
	];

	public function getAllCards(){
		return $this->filter(['active' => 1]);
	}

	public function getCreditCards(){
		return $this->filter(['type' => 1, 'active' => 1]);
	}

	public function getDebitCards(){
		return $this->filter(['type:>' => 1, 'active' => 1]);
	}

	public function getSample(){
		$this->filter(['[NON]type:>' => 1, '[OR_1]active' => 1, '[OR_2]active' => 2, '[OR]id_card:>' => 1]);
		return $this->getQueryString();
	}

} 