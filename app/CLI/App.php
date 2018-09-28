<?php

namespace App\CLI;

use Roducks\Framework\CLI;
use Roducks\Interfaces\CLIInterface;

class App extends CLI implements CLIInterface
{
  public function config()
  {
    $this->info(PAGE_TITLE);
    parent::output();
  }
}
