<?php

namespace App\Step;

use App\Command\CommandInterface;

/**
 * Check settings for exists.
 */
class WriteSettingsChmodStep extends StepBase {

  /**
   * Run.
   */
  public function run() : bool {
    $this->command->msg('Step: Chmod');

    $cmd = sprintf(
      'chmod 755 %s', $this->command->settingsPath
    );
    $result = $this->command->runProcess($cmd);

    $this->command->logExecute(
      $result['success'] ?? FALSE,
      'Chmod 755 dir success',
      $result['error'] ?? 'Chmod 755 dir failed'
    );

    if ($result['success'] ?? FALSE) {
      $cmd = sprintf(
        'chmod 644 %s', $this->command->settingsFilePath
      );
      $result = $this->command->runProcess($cmd);

      $this->command->logExecute(
        $result['success'] ?? FALSE,
        'Chmod 644 file success',
        $result['error'] ?? 'Chmod 644 file failed'
      );
      return $result['success'] ?? FALSE;
    }

    return FALSE;
  }

}
