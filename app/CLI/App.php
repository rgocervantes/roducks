<?php

namespace App\CLI;

use Roducks\Framework\CLI;
use Roducks\Interfaces\CLIInterface;

class App extends CLI implements CLIInterface
{
  public function run()
  {
    $this->outputLine($this->colorGreen("- " . PAGE_TITLE));
  }
}
