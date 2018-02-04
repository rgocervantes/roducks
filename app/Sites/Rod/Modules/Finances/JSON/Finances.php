<?php

namespace App\Sites\Rod\Modules\Finances\JSON;
 
use Roducks\Page\JSON;
use Roducks\Page\Block;
use Roducks\Framework\Helper;
use Roducks\Framework\Language;
use Roducks\Framework\Form;
use Roducks\Libs\Utils\Date;
use App\Sites\Rod\Modules\Finances\Helper\Finances as FinancesHelper;

class Finances extends JSON {
 
    private $_helper;
 
	protected $_dispatchUrl = true;

    public function __construct(array $settings){
        parent::__construct($settings);
          
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = FinancesHelper::init(); // Initialize helper
    }

	/*
	*
	*/
	private function _getFields(){

		$this->post->required();

		$id_card = $this->post->select('id_card');
		$period = $this->post->select('period');
		$total = $this->post->text('total');
		$amount = ($period > 1) ? ($total / $period) : $total;
		$amount = Helper::fixDecimal($amount);
		
		$data = [
			'id_card' => $id_card,
			'title' => $this->post->text('title',""),
			'description' => $this->post->textarea('description',""),
			'period' => $period,
			'amount' => $amount,
			'total' => $total,
			'payment_date' => $this->post->text('payment_date')
		];

		$form = Form::validation([
			Form::filter(Form::FILTER_INTEGER, $data['id_card'], "Invalid CardId."),
			Form::filter(Form::FILTER_DECIMAL, $data['amount'], "Invalid Amount."),
			Form::filter(Form::FILTER_DECIMAL, $data['total'], "Invalid Total."),
			Form::filter(Form::FILTER_DATE_YYYY_MM_DD, $data['payment_date'], "Invalid Payment Date."),
			//Form::filter(Form::values([8,9]), $data['period'], "No match!", "period"),
			//Form::filter(Form::match(7), $data['id_card'], "No is a card id.")
			//Form::filter(Form::regexp('/^\d+\.\d{2}$/'), $data['total'], "Invalid Total")
			Form::filter(Form::greaterThan(0.00), $data['total'], "Total must be greater than \$0.00")
		]);

		if(!Form::isValid($form)){
			$this->data("field_name", $form['error']['field']);
			$this->setError(1, $form['error']['message']);
			parent::output();
		}

		$payment_data = $this->_helper->setPayment($data['id_card'], $data['payment_date'], $data['period']);
		$data['settlement_date'] = $payment_data['settlement_date'];

		return $data;

	}

	public function remove(){

		$this->post->required();

		$id = $this->post->param("id", 0);
		$tx = $this->_helper->deletePayment($id);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

	public function visibility(){

		$this->post->required();

		$id = $this->post->param("id", 0);
		$value = $this->post->param("value", 0);

		$data = ['active' => $value];

		$tx = $this->_helper->setRecurrentPaymentData($id, $data);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

	public function paid(){

		$this->post->required();

		$id = $this->post->param("id", 0);
		$month = $this->post->param("month", 0);

		$data = ['month' => $month];

		$tx = $this->_helper->setRecurrentPaymentData($id, $data);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

	public function paidCard(){

		$this->post->required();

		$id = $this->post->param("id", 0);
		$month = $this->post->param("month", 0);

		$data = ['month' => $month];

		$tx = $this->_helper->setCardData($id, $data);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

	/*
	*	@type POST
	*/
	public function simulation(){

		$fields = $this->_getFields();
		$this->data("fields", $fields);

		parent::output();
	}

	public function simulationCallback(){

		$this->post->required();

		$post = $this->post->param("fields", []);

		$paymentDate = Date::getDateArray($post['payment_date']);
		$startMonth = $paymentDate['m'];
		$month = intval(Date::getCurrentMonth());
		//$post['totals'] = 0;
		
		$settlement = FinancesHelper::getSettlementData($post['period'], $post['settlement_date'], $month);
		
		$date = Date::getDateArray($settlement['start_date']);
		$year = $date['y'];
		$month = $date['m'];
		$post['monthLabel'] = Date::getMonthLabel($month, Language::get());
		$post['payment_date'] = Date::getDateFormat($post['payment_date'], Language::get());
        $post['totals'] = $this->_helper->getPaymentsTotals($post['id_card'], $year, $month);

		Block::load("finances/alert", ['data' => $settlement]);	
		Block::load("finances/simulation", ['data' => $post]);
	}

	/*
	*	@type POST
	*/
	public function insert(){

		$fields = $this->_getFields();
		$tx = $this->_helper->addPayment($fields);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

	/*
	*	@type POST
	*/
	public function update($id){

		$fields = $this->_getFields();
		$tx = $this->_helper->updatePayment($id, $fields);

		if($tx === false){
			$this->setError(1, "There was an error");
		}

		parent::output();
	}

}
