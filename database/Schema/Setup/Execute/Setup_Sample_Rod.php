<?php

namespace DB\Schema\Setup\Execute;

use Setup;

class Setup_Sample_Rod extends Setup
{
  const COMMENTS = "Sample setup";

  public function execute()
  {
    return [
      "Setup_2017_09_02_Rod",
    ];
  }

}
