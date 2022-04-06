<?php

namespace App\Step;

/**
 * Write settings password.
 */
class WriteSettingsWritePasswordStep extends StepBase {

  const ASCII_NEW_LINE = 10;

  /**
   * Run.
   */
  public function run() : bool {
    $passwordLine = sprintf(
      '%c\$databases[\"default\"][\"default\"][\"password\"] = \"%s\";%c',
      self::ASCII_NEW_LINE, $_ENV['DBPASS'] ?? '',  self::ASCII_NEW_LINE
    );
    $cmd = sprintf(
      'echo "%s" >> %s', $passwordLine, $this->command->settingsFilePath
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Password string recorded',
      $result['error'] ?? 'Error writing line to file'
    );
    return $result['success'] ?? FALSE;
  }

}
