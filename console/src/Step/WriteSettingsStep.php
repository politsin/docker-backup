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
    $this->command->sendMqttMessage('START', 'WriteSettingsStep');
    $this->command->sendMessage('Write settings php');

    $this->command->settingsPath = '/var/www/html/sites/default';
    $this->command->settingsFileName = 'settings.php';

    if (!(new WriteSettingsFileExistsStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new WriteSettingsChmodStep($this->command))->run()) {
      return FALSE;
    }
    elseif (!(new WriteSettingsWritePasswordStep($this->command))->run()) {
      return FALSE;
    }

    $this->command->sendMqttMessage('FINISH', 'WriteSettingsStep');
    return TRUE;
  }

}
