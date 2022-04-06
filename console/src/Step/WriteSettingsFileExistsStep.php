<?php

namespace App\Step;

/**
 * Check settings for exists.
 */
class WriteSettingsFileExistsStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->settingsFilePath = implode('/', [
      $this->command->settingsPath,
      $this->command->settingsFileName,
    ]);

    $cmd = sprintf('ls %s', $this->command->settingsFilePath);
    $result = $this->command->runProcess($cmd);
    $this->command->logExecute(
      $result['success'] ?? FALSE,
      sprintf('File "%s" exists', $this->command->settingsFileName),
      $result['error'] ?? 'Settings file not exists'
    );

    return $result['success'] ?? FALSE;
  }

}
