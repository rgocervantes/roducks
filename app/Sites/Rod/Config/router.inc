<?php

use Roducks\Framework\Dispatch;
 
return [
	/*
	------------------------------------------------------------------
	|	DEFAULT
	------------------------------------------------------------------
	*/
	'/' => [
	  'dispatch' => Dispatch::page("finances","index")
	],
	'/sample' => [
	  'dispatch' => Dispatch::page("finances","sample")
	],
	/*
	------------------------------------------------------------------
	|	API
	------------------------------------------------------------------
	*/
	'/api' => [
		'path' => [
			'/v1/auth' => [
				'dispatch' => Dispatch::api("auth","credentials"),
				'cors' => "*",
				//'headers' => ["X-Custom-Header"],
				'POST' => [
					'email' => Dispatch::PARAM_EMAIL,
					'password' => Dispatch::PARAM_PASSWORD
				]
			],
			'/v1/countries' => [
				'dispatch' => Dispatch::api("countries"),
				'jwt' => true,

				'GET' => [
					'iso' => Dispatch::values(['mx','us'])
				],
				'POST' => [
					'name' => Dispatch::PARAM_STRING
				]
			],
			'/v1/countries/(?P<id>\d+)' => [
				'dispatch' => Dispatch::api("countries"),
				'jwt' => true,
				'headers' => ["X-App-Id"],
				'GET' => [],
				'PUT' => [
					'status' => Dispatch::PARAM_STRING
				],
				'DELETE' => []
			]
		]
	],
	/*
	------------------------------------------------------------------
	|	FINANCES
	------------------------------------------------------------------
	*/
	'/finances' => [
		'dispatch' => Dispatch::page("finances","index"),
		'path' => [
			'/scheduled' => [
				'dispatch' => Dispatch::page("finances", "scheduled"),
				'GET' => [
					'year' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{4}$/'],
					'month' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{1,2}$/'],
					'cardId' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],
			'/payments' => [
				'dispatch' => Dispatch::page("finances", "payments"),
				'GET' => [
					'year' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{4}$/'],
					'month' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{1,2}$/'],
					'cardId' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],
			'/shopping' => [
				'dispatch' => Dispatch::page("finances", "shopping"),
				'GET' => [
					'year' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{4}$/'],
					'month' => [Dispatch::OPTIONAL_PARAM_INTEGER => '/^\d{1,2}$/'],
					'cardId' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],
			'/simulation' => [
				'dispatch' => Dispatch::page("finances", "simulation")
			],
			'/add' => [
				'dispatch' => Dispatch::page("finances", "add")
			],
			'/edit/id/(?P<paymentId>\d+)' => [
				'dispatch' => Dispatch::page("finances", "edit")
			]

		]

	],

];