<?php

namespace App\Sites\Rod\Modules\Finances\Helper;
 
use Roducks\Page\HelperPage;
use Roducks\Page\Block;
use Roducks\Framework\Helper;
use Roducks\Libs\Utils\Date;
use App\Models\Finances\cards as CardsTable;
use App\Models\Finances\payments as PaymentsTable;
use App\Models\Finances\RecurrentPayments as RecurrentPaymentsTable;
 
class Finances extends HelperPage {

	static function getUrl($path = ""){
		return "/finances{$path}";
	}

	static function getJsonUrl($path){
		return "/_json" . self::getUrl($path);
	}

	static function getShoppingUrl(){
		return self::getUrl("/shopping");
	}

	static function getPaymentsUrl(){
		return self::getUrl("/payments");
	}	

	static function getScheduledUrl(){
		return self::getUrl("/scheduled");
	}

	static function getEditUrl($id){
		return self::getUrl("/edit/id/{$id}");
	}

	static function getAddUrl(){
		return self::getUrl("/add");
	}

	static function getSimulationUrl(){
		return self::getUrl("/simulation");
	}

	static function getRemoveUrl(){
		return self::getJsonUrl("/remove");
	}

	static function getVisibilityUrl(){
		return self::getJsonUrl("/visibility");
	}

	static function getPaidUrl(){
		return self::getJsonUrl("/paid");
	}

	static function getPaidCardUrl(){
		return self::getJsonUrl("/paid-card");
	}

	static function getCardTitle($title, $card){
		return "{$title} ****{$card}";
	}

	static function getPeriod($period){

		$label = "Pago Normal";
		$color = "warning";

		if($period > 1){
			$label = "<b>{$period}</b> MSI.";
			$color = "info";
		}

		Block::load("finances/period", ['color' => $color, 'label' => $label]);
	}

	static function getDiff($date1, $date2){

		$d1 = Date::getDateArray($date1);
		$d2 = Date::getDateArray($date2);

		$years = 0;
		$months = 0;

		if($d1['y'] == $d2['y']){ // EQUALS

			if($d2['m'] > $d1['m']){
				$months = $d2['m'] - $d1['m'];
			}
			
		}else if($d2['y'] > $d1['y']){ // GREATER THAN

			$years = $d2['y'] - $d1['y'];

			if($d1['m'] != $d2['m']){
				$years--;

				$months = 12 - $d1['m'];
				$months += $d2['m'];

			}	
		}

		return [
			'years'  => $years,
			'months' => $months
		];

	}

	static function getSettlementData($period, $settlement_date, $selected, $isSettlement = false){

		$period--;

		$start_date = Date::subtractMonths($settlement_date, $period);

		$diff = self::getDiff(date('Y-m-d', strtotime( implode("-",[$selected['year'], Helper::addZero($selected['month']), '01'] ) ) ), $settlement_date);

		$rt = $diff['months'];
		$alert = "Error";
		$color = "danger";

		if($diff['months'] == 0){	
			$alert = "Se liquida este mes.";
			$color = "success";
		}else{
			$months = ($rt > 1) ? "meses" : "mes";
			$alert = "En {$rt} {$months} más se liquida.";
			$color = "info";
		}

		if($isSettlement){
			if(Date::getCurrentDate() > $settlement_date){
				$alert = "Liquidado.";
				$color = "warning";
			}
		}

		return [
			'period' => $rt,
			'start' => Date::getDateFormat($start_date,"es"),
			'end' => Date::getDateFormat($settlement_date,"es"),
			'start_date' => $start_date,
			'settlement_date' => $settlement_date,
			'alert' => $alert,
			'color' => $color
		];

	}

	static function getSettlementDate($period, $settlement_date, $selected, $notice = true, $isSettlement = false){

		if($period > 1 || !$notice){

			$data = self::getSettlementData($period, $settlement_date, $selected, $isSettlement);
	
			Block::load("finances/alert", ['data' => $data]);
		}

	}

	private function _db(){
		$config = $this->getModuleConfig("finances");
		$conn = $config['DB'];
		return $this->openDb($conn);
	}

	public function getCardsModel(){
		$db = $this->db();
		return CardsTable::open($db);
	}

	/**
	*	@param $date int
	*
	*	@return string
	*/
	public function setPayment($id_card, $date, $period){
		
		if(!preg_match(Helper::VALID_DATE_YYYY_MM_DD, $date, $d) ) return [];

		$day = intval($d[3]);

		$card = $this->getCardById($id_card);

		// Si el día actual es mayor o igual a la fecha de corte, significa que el pago se paga en 2 meses a partir del mes actual
		$inc = ($day >= $card['cut_day']) ? 2 : 1;
		
		// convertimos fecha string a fecha
		$newDate = date('Y-m-d', strtotime(implode("-",array($d[1],$d[2],Helper::addZero($card['payment_day'])))));
		
		// incrementamos los meses para obtener la fecha del primer pago
		$payment_date = date('Y-m-d', strtotime("$newDate +$inc month"));
		
		// si el periodo es a meses sin intereres
		if($period > 1){

			// Si ya cortó
			if($inc > 1){
				// incrementa la fecha 1 mes adelante +
				$payment_datex = date('Y-m-d', strtotime("$newDate +1 month"));
				// los meses en que se pagará
				$settlement_date = date('Y-m-d', strtotime("$payment_datex +$period month"));			
			}else{
				// Solo incrementa los meses del periodo
				$settlement_date = date('Y-m-d', strtotime("$newDate +$period month"));			
			}
		}else{
			// si es pago regular
			$settlement_date = $payment_date;			
		}

		return [
			'buy_date' => $date,
			'payment_date' => $payment_date,
			'settlement_date' => $settlement_date
		];

	}

	public function getPeriods(){
		$config = $this->getModuleConfig("finances");
		$periods = $config['PERIODS'];
		$ret = [];

		foreach ($periods as $period) {
			$ret[] = [
				'text' => ($period > 1) ? "{$period} Meses sin interéses" : "Pago Normal",
				'value' => $period
			];
		}

		return $ret;
	}

	public function getShoppingListByDate($cardId, $year, $month){
		$db = $this->_db();
		return PaymentsTable::open($db)->getShoppingListByDate($cardId, $year, $month);
	}

	public function getScheduledPaymentListByDate($cardId, $year, $month){
		$db = $this->_db();
		return PaymentsTable::open($db)->getScheduledPaymentListByDate($cardId, $year, $month);
	}

	public function getScheduledPaymentListByYear($cardId, $year){
		$db = $this->_db();
		return PaymentsTable::open($db)->getScheduledPaymentListByYear($cardId, $year);
	}

	public function getNormalPaymentListByDate($cardId, $year, $month){
		$db = $this->_db();
		return PaymentsTable::open($db)->getNormalPaymentListByDate($cardId, $year, $month);
	}

	public function addPayment($fields){
		$db = $this->_db();
		return PaymentsTable::open($db)->insert($fields);
	}

	public function updatePayment($id, $fields){
		$db = $this->_db();
		return PaymentsTable::open($db)->update($id, $fields);
	}	

	public function deletePayment($id){
		$db = $this->_db();
		$PaymentsTable = PaymentsTable::open($db)->load($id);
		return $PaymentsTable->delete(['period' => 1]);
	}

	public function getPaymentById($id){
		$db = $this->_db();
		return PaymentsTable::open($db)->row($id);
	}

	public function getCreditCards(){
		$db = $this->_db();
		return CardsTable::open($db)->getCreditCards();
	}

	public function getAllCards(){
		$db = $this->_db();
		return CardsTable::open($db)->getAllCards();
	}

	public function getCardById($id){
		$db = $this->_db();
		return CardsTable::open($db)->row($id);
	}

	public function setCardData($id, array $data){
		$db = $this->_db();
		$data['updated_date'] = CardsTable::NOW;
		return CardsTable::open($db)->update($id, $data);
	}

	public function getRecurrentPaymentsList($type){
		$db = $this->_db();
		return RecurrentPaymentsTable::open($db)->getList($type);
	}

	public function setRecurrentPaymentData($id, $data){
		$db = $this->_db();
		$data['updated_date'] = RecurrentPaymentsTable::NOW;
		return RecurrentPaymentsTable::open($db)->update($id, $data);
	}

	public function getPaymentsTotals($id_card, $year, $month){

		$totals = 0;
        $data = $this->getScheduledPaymentListByDate($id_card, $year, $month);
        $dataAlt = $this->getNormalPaymentListByDate($id_card, $year, $month);

        if($data->rows()): while($row = $data->fetch()):
        	$totals += $row['amount'];
        endwhile; endif;	
        
        if($dataAlt->rows()): while($row = $dataAlt->fetch()):
        	$totals += $row['amount'];
        endwhile; endif;

        return $totals;

	}

}