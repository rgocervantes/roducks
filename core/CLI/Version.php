<?php

namespace Roducks\CLI;

use Roducks\Framework\CLI;
use Roducks\Interfaces\CLIInterface;

class Version extends CLI implements CLIInterface
{
  public function run()
  {
    $this->dialogInfo(null);
    $this->info("[x]" . RDKS_VERSION);
    parent::output();
  }
}
