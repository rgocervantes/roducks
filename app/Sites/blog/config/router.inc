<?php

use rdks\core\framework\Dispatch;

$router = [
	'/' => [
		'dispatch' => Dispatch::page("posts","listing"),
		'GET' => [
			'page' => Dispatch::OPTIONAL_PARAM_INTEGER
		]
	],
	'/post/id/(?P<postId>\d+)' => [
		'dispatch' => Dispatch::page("posts","by-post-id")
	],
	'/posts/category/id/(?P<categoryId>\d+)' => [
		'dispatch' => Dispatch::page("posts","by-category-id"),
		'GET' => [
			'page' => Dispatch::OPTIONAL_PARAM_INTEGER
		]
	],	
	'/json/latest-post\.json' => [
		'dispatch' => Dispatch::json("posts","latest"),
		'GET' => [
			'page' => Dispatch::PARAM_STRING
		]
	]
];