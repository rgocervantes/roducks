<?php

/**
 *	@package FinancesModel
 *
 */

namespace DB\Models\Finances;

use Model;
use Roducks\Framework\Helper;

class payments extends Model {

	var $id = "id_payment",
		$fields = [
		'id_card' 		 	=> Model::TYPE_INTEGER,
		'title' 		 	=> Model::TYPE_VARCHAR,
		'description' 	 	=> Model::TYPE_TEXT,
		'period' 		 	=> Model::TYPE_INTEGER,
		'amount' 		 	=> Model::TYPE_DECIMAL,
		'total' 		 	=> Model::TYPE_DECIMAL,
		'payment_date'	 	=> Model::TYPE_DATE,
		'settlement_date'	=> Model::TYPE_DATE,		
		'paid'			 	=> Model::TYPE_BOOL	
	];

	public function getScheduledPaymentListByDate($cardId, $year, $month){
		
		$mx = ($month-1);
		$mx = Helper::addZero($mx);
		$mm = Helper::addZero($month);
		$yy = ($year - 1);
		$cutDate = "{$year}-{$mm}-12";
		$initDate = "{$year}-{$mm}-01";
		$lastDate = "{$year}-{$mx}-12";

		$cutDatex = "{$year}-{$mx}-12";

		return 
			$this->filter([
				'id_card' => $cardId,
				'period:>' => 1,
				'[BEGIN_COND]' => "((",
					'[NON_1]payment_date:date:year' => $year,
					'[AND_1]settlement_date:date:year' => $year,
					'[AND_2]payment_date:date:<=' => $initDate,
					'[AND_3]settlement_date:date:>' => $initDate,
					'[AND_4]payment_date:date:<=' => $cutDatex,
				'[COND_1]' => ") OR (",
					'[NON_2]payment_date:date:year' => $year,
					'[AND_5]settlement_date:date:year:>' => $year,
					'[AND_7]payment_date:date:<' => $cutDatex,
				'[COND_2]' => ") OR (",
					'[NON_3]settlement_date:date:year' => $year,
					'[AND_8]settlement_date:date:>=' => $initDate,
					'[AND_9]payment_date:date:<' => $initDate,
					'[AND_10]payment_date:date:year' => $year,
					'[AND_34]payment_date:date:<=' => $cutDatex,
				'[COND_3]' => ") OR (",
					'[NON_4]settlement_date:date:year' => $year,
					'[AND_11]payment_date:date:<' => $lastDate,
					'[AND_12]payment_date:date:<>' => $cutDate,
					'[AND_13]settlement_date:date:>=' => $initDate,
				'[END_COND]' => "))"
			]);

		echo $this->getQueryString();
		exit;

	}

	public function getScheduledPaymentListByYear($cardId, $year){

		return
			$this->filter([
				'id_card' => $cardId,
				'period:>' => 1,
				'payment_date:date:year' => $year
			]);

	}

	public function getNormalPaymentListByDate($cardId, $year, $month){

		$this->orderBy(["payment_date" => "ASC"]);
		
		return 
			$this->filter([
				'id_card' => $cardId,
				'period' => 1,
				'settlement_date:date:year' => $year,
				'settlement_date:date:month' => $month,				
			]);
	}

	public function getShoppingListByDate($cardId, $year, $month){

		$this->orderBy(["payment_date" => "ASC"]);
		
		return 
			$this->filter([
				'id_card' => $cardId,
				'payment_date:date:year' => $year,
				'payment_date:date:month' => $month
			]);
	}

}