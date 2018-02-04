<?php

namespace App\Sites\Rod\Api; 

use Roducks\Libs\Request\Http;

class Countries extends Auth
{

	var $iso = "mx";

	/**
	 * @type GET
	 */
	public function row($id)
	{	

		$this->data('app_id', Http::getRequestHeader('app_id'));
		$this->data('id', $id);
		$this->data('token', $this->getToken());

		$this->output();
	}

	/**
	 * @type GET
	 */
	public function getAll(\stdClass $request)
	{
		$data = ['countries' => ["MX", "US","BR"]];
		$data['iso'] = $request->iso;
		$this->data($data);
		
		$this->output();
	}

	/**
	 * @type POST
	 */
	public function store(\stdClass $request)
	{	

		$this->data('name', $request->name);
	
		$this->output();
	}

	/**
	 * @type PUT
	 */
	public function update(\stdClass $request, $id)
	{	

		$this->data('id', $id);
		$this->data('request', $request);

		$this->output();
	}

	/**
	 * @type DELETE
	 */
	public function remove(\stdClass $request, $id)
	{	
		$this->data('id', $id);
		$this->data('request', $request);
	
		$this->output();
	}

}
