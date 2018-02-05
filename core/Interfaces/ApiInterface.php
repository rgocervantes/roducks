<?php

namespace Roducks\Interfaces;

Interface ApiInterface
{
	/**
	 * @type GET
	 */
	public function row($id);

	/**
	 * @type GET
	 */
	public function getAll(\stdClass $request);

	/**
	 * @type POST
	 */
	public function store(\stdClass $request);

	/**
	 * @type PUT
	 */
	public function update(\stdClass $request, $id);

	/**
	 * @type DELETE
	 */
	public function remove(\stdClass $request, $id);

}