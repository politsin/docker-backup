<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * StepBase.
 */
class StepBase {

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    $this->command = $command;
  }

}
