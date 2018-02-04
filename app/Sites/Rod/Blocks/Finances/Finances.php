<?php

namespace App\Sites\Rod\Blocks\Finances;
 
use Roducks\Page\Block;
 
class Finances extends Block {

	var $id = 1;

	public function alert($data){
		
		$this->view->data($data);
		$this->view->load("alert");

		return $this->view->output();
	}

	public function period($color, $label){
		
		$this->view->data("color", $color);
		$this->view->data("label", $label);
		$this->view->load("period");

		return $this->view->output();
	}	

	public function simulation($data){

		$this->view->data($data);
		$this->view->load("simulation");

		return $this->view->output();
	}

	public function sample($title){

		echo "{$title} " . $this->id;
	}

}