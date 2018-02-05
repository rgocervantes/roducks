<?php

namespace Roducks\Interfaces;

Interface PageInterface
{
	public function __construct(array $settings, \Roducks\Page\View $view);
}
