<?php

namespace App\Step;

/**
 * Check settings for exists.
 */
class WriteSettingsChmodStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $cmd = sprintf('chmod 755 %s', $this->command->settingsPath);
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Chmod 755 dir success',
      $result['error'] ?? 'Chmod 755 dir failed'
    );

    if (empty($result['success'])) {
      return FALSE;
    }

    $cmd = sprintf('chmod 644 %s', $this->command->settingsFilePath);
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Chmod 644 file success',
      $result['error'] ?? 'Chmod 644 file failed'
    );
    return $result['success'] ?? FALSE;
  }

}
