<?php

namespace App\CLI;

use Roducks\Framework\CLI;
use Roducks\Framework\Settings;
use Roducks\Interfaces\CLIInterface;

class App extends CLI implements CLIInterface
{
  public function name()
  {
    $this->outputLine(
      $this->colorYellow("Site name:") .
      $this->colorGreen(Settings::getPageTitle())
    );
  }

  public function emailFrom()
  {
    $this->outputLine(
      $this->colorYellow("Email from:") .
      $this->colorGreen(Settings::getEmailFrom())
    );
  }

  public function emailTo()
  {
    $this->outputLine(
      $this->colorYellow("Email to:") .
      $this->colorGreen(Settings::getEmailTo())
    );
  }

}
