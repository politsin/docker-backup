<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Write settings php.
 */
class WriteSettingsStep extends StepBase {

  /**
   * Construct.
   */
  public function __construct(CommandInterface $command) {
    parent::__construct($command);

    $this->command->settingsPath = '/var/www/html/sites/default';
    $this->command->settingsFileName = 'settings.php';
  }

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Step: Write settings php');

    if (!(new WriteSettingsFileExistsStep($this->command))->run()) {
      return FALSE;
    }
    (new WriteSettingsChmodStep($this->command))->run();
    (new WriteSettingsWritePasswordStep($this->command))->run();

    return TRUE;
  }

}
