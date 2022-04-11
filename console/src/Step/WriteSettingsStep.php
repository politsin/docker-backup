<?php

namespace App\Step;

/**
 * Write settings php.
 */
class WriteSettingsStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Write settings php');

    $this->command->settingsPath = '/var/www/html/sites/default';
    $this->command->settingsFileName = 'settings.php';

    if (!(new WriteSettingsFileExistsStep($this->command))->run()) {
      return FALSE;
    }
    (new WriteSettingsChmodStep($this->command))->run();
    (new WriteSettingsWritePasswordStep($this->command))->run();

    return TRUE;
  }

}
