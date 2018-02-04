<?php

namespace App\Sites\Front\Services;
 
use Roducks\Page\Service;
 
class Countries extends Service {
 
    protected $_dispatchUrl = true;
 
    private function _getList(){
        return ["US","MX","FR","BR","AR"];
    }
 
    public function listing(){
        $this->data("list", $this->_getList());
 
        parent::output();
    }
 
    public function getList(){
        return $this->_getList();
    }

}