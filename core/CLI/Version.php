<?php

namespace Roducks\CLI;

use Roducks\Framework\CLI;
use Roducks\Interfaces\CLIInterface;

class Version extends CLI implements CLIInterface
{
  public function run()
  {
    echo $this->colorGreen(RDKS_VERSION) . "\n";
    parent::output();
  }
}
