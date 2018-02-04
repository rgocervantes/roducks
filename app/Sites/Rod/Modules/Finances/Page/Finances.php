<?php

namespace App\Sites\Rod\Modules\Finances\Page;
 
use Roducks\Page\Page;
use Roducks\Page\View;
use Roducks\Framework\Language;
use Roducks\Framework\Helper;
use Roducks\Libs\Utils\Date;
use App\Sites\Rod\Modules\Finances\Helper\Finances as FinancesHelper;
use App\Models\Users\Users as UsersTable;
 
class Finances extends Page {
 
    var $year = 0,
        $month = 0,
        $cardId = 0;

    private $_helper,
            $_rule = false;

    private function _params(){

        if($this->year == 0){
            $this->_rule = true;
            $this->year = Date::getCurrentYear();
        }

        if($this->month == 0){
            $this->_rule = true;
            $this->month = intval(Date::getCurrentMonth());
        }

    }

    private function _showCurrentPayment($card){

        $this->isData($card);

        $today = "";

        if($this->_rule){
            $month = intval(Date::getCurrentMonth());
            $day = intval(Date::getCurrentDay());

            $today = Date::getCurrentDateFormatLong(Language::get());
            

            if($day > intval($card['payment_day'])){

                if($month == 12){
                    $month = 1;
                    $this->year++;
                } else {
                    $month++;
                }

                $this->month = $month;
            }
        }

        $this->view->data("today", $today);

    }

    private function _getRow(){
        return [
            'title' => "",
            'description' => "",
            'period' => 1,
            'total' => "",
            'id_card' => 0,
            'payment_date' => Date::getCurrentDate(),
            'payment_date_format' => Date::getCurrentDate("/", false)
        ];
    }

    private function _form($row, $action, $edit, $submit){

        $this->view->assets->scriptsInline(["form"]);
        $cards = ($submit) ? $this->_helper->getAllCards() : $this->_helper->getCreditCards();

        $this->view->data("row", $row);
        $this->view->data("periods", $this->_helper->getPeriods());
        $this->view->data("cards", $cards);
        $this->view->data("action", $action);
        $this->view->data("edit", $edit);
        $this->view->data("submit", $submit);

        $date = Date::getDateArray($row['payment_date']);

        $this->view->data("year", $date['y']);
        $this->view->data("month", $date['m']);
        $this->view->data("day", $date['d']);

        $this->view->load("form");
    }
 
    public function __construct(array $settings, View $view){
        parent::__construct($settings, $view);
          
        // You will be able to call all the helper's PUBLIC methods
        $this->_helper = FinancesHelper::init(); // Initialize helper

        $this->_params();
    }

    public function _index(){
        $db = $this->db();
        $user = UsersTable::open($db);
/*        $row = $user->row(1); // PK
         
        if($user->getId()){
            $name = $row['first_name'];
            $email = $row['email'];
            echo $name;
            echo "<br>";
            echo $email;
        } else {
            echo "Invalid Id.";
        }
*/
        $user->where(['active' => 1]);
        $subnames = $user->getUniques('last_name');

        echo $user->getQueryString();
   /*      
        $subnames = [];
        if($user->rows()): while($row = $user->fetch()):
                $subnames[] = $row['last_name'];
        endwhile; endif;
*/
        //\Roducks\framework\Helper::pre($subnames);

    }
/*
    public function index(){
        echo $this->_helper->getCardsModel()->getSample();
    }
*/

    public function sample()
    {

        $this->view->load("sample");

        return $this->view->output();
    }

    public function index(){

        $dataMain = $this->_helper->getRecurrentPaymentsList('fixed');
        $dataSecundary = $this->_helper->getRecurrentPaymentsList('variable');

        $this->view->assets->scriptsInline(["finances","grid","popover"]);
        $this->view->assets->scriptsOnReady(["finances.ready"]);

        $today = Date::getCurrentDateFormatLong(Language::get());
        $day = intval(Date::getCurrentDay());
        $month = intval(Date::getCurrentMonth());
        $year = intval(Date::getCurrentYear());
        $lastDay = Date::getLastDaysOfCurrentMonth();

        $nextMonth = $month+1;

        if($month == 12){
            $year++;
            $nextMonth = 1;
        }
        
        $monthLabel = Date::getMonthLabel($month, Language::get());
        $nextMonthLabel = Date::getMonthLabel($nextMonth, Language::get());

        $cards = $this->_helper->getCreditCards();
        $dataPlus = [];

        if($cards->rows()): while($card = $cards->fetch()):
            $dataPlus[] = [
                'id_card' => $card['id_card'],
                'title' => FinancesHelper::getCardTitle($card['title'],$card['card_number']) . " ({$nextMonthLabel})",
                'total' => $this->_helper->getPaymentsTotals($card['id_card'], $year, $nextMonth),
                'day' => $lastDay,
                'month' => $card['month']
            ];
        endwhile; endif;
 
        $this->view->title("Finanzas");
        $this->view->data("title", "Pagos Recurrentes de {$monthLabel}");
        $this->view->data("dataMain", $dataMain);
        $this->view->data("dataSecundary", $dataSecundary);
        $this->view->data("dataPlus", $dataPlus);
        $this->view->data("day", $day);
        $this->view->data("month", $month);
        $this->view->data("today", $today);
        $this->view->tpl("urlShopping", "");
        $this->view->load("listing-recurrent");

        return $this->view->output();
    }

    public function scheduled(){
        if($this->year < 2000){
            return $this->view->error("public", __METHOD__, "Invalid GET Param 'year'");
        }

        $this->view->tpl("urlShopping", FinancesHelper::getScheduledUrl());

        if($this->cardId == 0){
            $this->view->assets->scriptsInline(["finances"]);
            $this->view->title("Tarjetas");
            $this->view->data("cards", $this->_helper->getCreditCards());
            $this->view->load("cards");

            return $this->view->output();
        }

        $card = $this->_helper->getCardById($this->cardId);

        if(!isset($card['id_card'])){
            if(!empty($card)){
                $this->view->load("cards");

                return $this->view->output();
            } else {
                $this->pageNotFound();
            }
        }

        $data = $this->_helper->getScheduledPaymentListByYear($this->cardId, $this->year);

        $this->view->assets->scriptsInline(["finances","grid","popover"]);
        $this->view->assets->scriptsOnReady(["finances.ready"]);
        
        $this->view->data("title", "Compras a MSI");
        $this->view->data("today", "");
        $this->view->data("selected", ["year" => $this->year, "month" => intval(Date::getCurrentMonth())]);
        $this->view->data("data", $data);
        $this->view->data("hasDataAlt", false);
        $this->view->data("isSettlement", true);
        $this->view->data("dataAlt", []);        
        
        $this->view->load("listing");

        return $this->view->output();
    }

    public function payments(){

        $this->view->tpl("urlShopping", FinancesHelper::getPaymentsUrl());

        if($this->cardId == 0){
            $this->view->assets->scriptsInline(["finances"]);
            $this->view->title("Tarjetas");
            $this->view->data("cards", $this->_helper->getCreditCards());
            $this->view->load("cards");

            return $this->view->output();
        }

        $card = $this->_helper->getCardById($this->cardId);
        $this->_showCurrentPayment($card);

        if(!isset($card['id_card'])){
            if(!empty($card)){
                $this->view->load("cards");

                return $this->view->output();
            } else {
                $this->pageNotFound();
            }
        }

        // ONLY Credit Cards are allowed 
        if($card['type'] > 1){
            $this->pageNotFound();
        }

        $data = $this->_helper->getScheduledPaymentListByDate($this->cardId, $this->year, $this->month);
        $dataAlt = $this->_helper->getNormalPaymentListByDate($this->cardId, $this->year, $this->month);
 
        $this->view->assets->scriptsInline(["finances","grid","popover"]);
        $this->view->assets->scriptsOnReady(["finances.ready"]);
        
        $paymentLimitDate = Date::getDateFormat(implode("-",[$this->year,Helper::addZero($this->month),Helper::addZero($card['payment_day'])]), Language::get());
        $this->view->data("title", Language::translate("Payment Limit Date: <u>{$paymentLimitDate}</u>","Fecha Limite de Pago: <u>{$paymentLimitDate}</u>"));
        $this->view->data("selected", ["year" => $this->year, "month" => $this->month]);
        $this->view->data("data", $data);
        $this->view->data("hasDataAlt", true);
        $this->view->data("isSettlement", false);
        $this->view->data("dataAlt", $dataAlt);

    	$this->view->load("listing");

    	return $this->view->output();
    }

    public function shopping(){

        if($this->year < 2000){
            return $this->view->error("public", __METHOD__, "Invalid GET Param 'year'");
        }

        if($this->month > 12){
            return $this->view->error("public", __METHOD__, "Invalid GET Param 'month' is greater than 12");
        }

        $this->view->tpl("urlShopping", FinancesHelper::getShoppingUrl());

        if($this->cardId == 0){
            $this->view->assets->scriptsInline(["finances"]);
            $this->view->title("Tarjetas");
            $this->view->data("cards", $this->_helper->getAllCards());
            $this->view->load("cards");

            return $this->view->output();
        }

        $card = $this->_helper->getCardById($this->cardId);

        if(!isset($card['id_card'])){
            if(!empty($card)){
                $this->view->load("cards");

                return $this->view->output();
            } else {
                $this->pageNotFound();
            }
        }

        $data = $this->_helper->getShoppingListByDate($this->cardId, $this->year, $this->month);
        $monthLabel = Date::getMonthLabel($this->month, Language::get());

        $this->view->assets->scriptsInline(["finances","grid","popover"]);
        $this->view->assets->scriptsOnReady(["finances.ready"]);
        
        $this->view->data("title", Language::translate("{$monthLabel}'s Shopping","Compras de {$monthLabel}"));
        $this->view->data("today", "");
        $this->view->data("selected", ["year" => $this->year, "month" => $this->month]);
        $this->view->data("data", $data);
        $this->view->data("hasDataAlt", false);
        $this->view->data("isSettlement", false);
        $this->view->data("dataAlt", []);        
        
        $this->view->load("listing");

        return $this->view->output();

    }

    public function simulation(){

        $this->view->title("Simulación");
        $this->view->assets->scriptsInline(["finances.form"]);
        $this->view->tpl("url", FinancesHelper::getJsonUrl("/simulation-callback"));

        $row = $this->_getRow();
        $this->_form($row, "/simulation", false, false);
        
        return $this->view->output();
    }

    public function add(){

        $this->view->title("Nueva Compra");

        $row = $this->_getRow();
        $this->_form($row, "/insert", false, true);

        return $this->view->output();
    }

    public function edit(){

        $this->view->title("Editar Compra");

        $id = $this->getUrlParam("paymentId");
        $row = $this->_helper->getPaymentById($id);
        $this->isData($row);
        $row['payment_date_format'] = Date::convertToDMY($row['payment_date'], "/");
        $this->_form($row, "/update/{$id}", true, true);

        return $this->view->output();
    }
}